import pandas as pd
import numpy as np
import json
import os
from datetime import timedelta

# ─────────────────────────────────────────────────────────────
# Root-cause & recommendation templates per metric
# ─────────────────────────────────────────────────────────────
ROOT_CAUSE_TEMPLATES = {
    "Daily_Revenue_RM_Mil": {
        "positive": "Revenue exceeded target — possible demand surge, new contract wins, or favourable commodity pricing.",
        "negative": "Revenue fell below target — potential demand contraction, project delays, or commodity price headwinds.",
    },
    "Daily_Gross_Profit_RM_Mil": {
        "positive": "Gross profit above target — improved cost efficiencies or higher-margin product mix.",
        "negative": "Gross profit below target — input cost escalation, margin compression, or unfavourable product mix.",
    },
    "Daily_Finance_Cost_RM_Mil": {
        "positive": "Finance costs spiked above target — likely unplanned loan drawdown, interest rate movement, or new lease obligations.",
        "negative": "Finance costs below target — early loan repayment or favourable refinancing.",
    },
    "FFB_Processed_Tonnes": {
        "positive": "Fresh fruit bunch processing exceeded target — peak harvest season or expanded capacity.",
        "negative": "FFB processed tonnes fell below target — machinery breakdown, flooding, or pest outbreak in plantation.",
    },
    "Patient_Admissions": {
        "positive": "Patient admissions above target — seasonal demand peak or expanded bed capacity.",
        "negative": "Patient admissions collapsed below target — IT/booking system outage, competitor pressure, or service disruption.",
    },
    "Units_Under_Construction": {
        "positive": "Construction units above target — accelerated project milestones.",
        "negative": "Construction units below target — regulatory delays, contractor issues, or material shortages.",
    },
    "Outlet_Transactions": {
        "positive": "Outlet transactions above target — promotional campaign success or new outlet openings.",
        "negative": "Outlet transactions below target — consumer sentiment decline or supply interruption.",
    },
}

ACTION_TEMPLATES = {
    "Daily_Revenue_RM_Mil": {
        "Critical": "Escalate to Group CFO and segment CEO immediately. Initiate emergency revenue audit, review top-5 contracts, and assess impact on Group annual guidance.",
        "High":     "Schedule segment finance review within 24 hrs. Reassess quarterly sales forecast and identify revenue recovery levers.",
    },
    "Daily_Gross_Profit_RM_Mil": {
        "Critical": "Convene emergency cost-review committee. Engage procurement on input cost hedging and review pricing strategy with commercial team.",
        "High":     "Review cost-of-sales breakdown. Identify top-3 margin leakage sources and submit remediation plan within 48 hrs.",
    },
    "Daily_Finance_Cost_RM_Mil": {
        "Critical": "Alert Group Treasurer and Board Finance Committee. Review all active credit facilities, identify unplanned drawdowns, and assess interest rate exposure.",
        "High":     "Review loan utilisation report. Confirm all drawdowns are approved and evaluate refinancing options to reduce cost.",
    },
    "FFB_Processed_Tonnes": {
        "Critical": "Deploy emergency maintenance team to affected mills. Notify Group Operations Director and activate business continuity plan. Assess crop loss impact.",
        "High":     "Increase mill monitoring frequency. Review equipment service logs and assess estate-level yield data for harvest disruptions.",
    },
    "Patient_Admissions": {
        "Critical": "Escalate to KPJ Healthcare CEO. Investigate booking system and IT infrastructure. Activate patient diversion protocol and notify Ministry of Health if required.",
        "High":     "Review admissions data by hospital unit. Identify specific wards/services affected and engage clinical operations team.",
    },
    "Units_Under_Construction": {
        "Critical": "Alert project directors and legal team. Review contractor performance bonds, assess regulatory compliance, and revise delivery timeline.",
        "High":     "Conduct site progress review. Identify bottlenecks in materials procurement and sub-contractor performance.",
    },
    "Outlet_Transactions": {
        "Critical": "Escalate to QSR Brands CEO. Review outlet-level POS data, identify affected locations, and launch targeted promotional response.",
        "High":     "Analyse transaction data by outlet cluster. Review stock availability and escalate to franchise operations team.",
    },
}

# ─────────────────────────────────────────────────────────────
# Cross-domain correlation pairs
# ─────────────────────────────────────────────────────────────
CORRELATION_PAIRS = [
    (
        "Agribusiness",
        "Real estate and infrastructure",
        "Agribusiness operational disruption detected within ±1 day — plantation output shortfall "
        "is likely constraining land-bank revenue and project cash flows in Real Estate & Infrastructure.",
        "Real estate & infrastructure anomaly detected within ±1 day — property revenue shortfall "
        "may signal cashflow pressure upstream to Agribusiness supply chain and shared financing facilities.",
    ),
    (
        "Wellness and healthcare",
        "Others",
        "KPJ Healthcare anomaly detected within ±1 day — patient admission / revenue drop may indicate "
        "systemic consumer confidence issue also affecting QSR outlet transactions.",
        "Others (QSR/Outlets) anomaly detected within ±1 day — consumer-facing disruption may cascade "
        "to Wellness & Healthcare demand signals given shared demographic exposure.",
    ),
    (
        "Agribusiness",
        "Others",
        "Agribusiness production anomaly detected within ±1 day — commodity supply pressure may "
        "affect raw-material costs for QSR/food-processing subsidiaries in Others segment.",
        "Others (QSR/Outlets) anomaly detected within ±1 day — downstream demand signals may "
        "indicate commodity pricing pressures feeding back to Agribusiness margins.",
    ),
]

# ─────────────────────────────────────────────────────────────
# Incident grouping rules — which metrics belong together
# ─────────────────────────────────────────────────────────────
INCIDENT_GROUPS = {
    "Agribusiness": {
        "Plantation Yield & Revenue Incident": ["FFB_Processed_Tonnes", "Daily_Revenue_RM_Mil", "Daily_Gross_Profit_RM_Mil"],
        "Agribusiness Finance Cost Incident":  ["Daily_Finance_Cost_RM_Mil"],
    },
    "Wellness and healthcare": {
        "Healthcare Operations Incident":      ["Patient_Admissions", "Daily_Revenue_RM_Mil", "Daily_Gross_Profit_RM_Mil"],
        "Healthcare Finance Cost Incident":    ["Daily_Finance_Cost_RM_Mil"],
    },
    "Real estate and infrastructure": {
        "Real Estate Development Incident":    ["Units_Under_Construction", "Daily_Revenue_RM_Mil", "Daily_Gross_Profit_RM_Mil"],
        "Real Estate Finance Cost Incident":   ["Daily_Finance_Cost_RM_Mil"],
    },
    "Others": {
        "QSR & Consumer Business Incident":   ["Outlet_Transactions", "Daily_Revenue_RM_Mil", "Daily_Gross_Profit_RM_Mil"],
        "Others Finance Cost Incident":       ["Daily_Finance_Cost_RM_Mil"],
    },
}

# Higher is better map
HIGHER_IS_BETTER = {
    "Daily_Revenue_RM_Mil":       True,
    "Daily_Gross_Profit_RM_Mil":  True,
    "Daily_Finance_Cost_RM_Mil":  False,
    "FFB_Processed_Tonnes":       True,
    "Patient_Admissions":         True,
    "Units_Under_Construction":   True,
    "Outlet_Transactions":        True,
}

METRIC_DISPLAY_NAMES = {
    "Daily_Revenue_RM_Mil":       "Daily Revenue (RM Mil)",
    "Daily_Gross_Profit_RM_Mil":  "Daily Gross Profit (RM Mil)",
    "Daily_Finance_Cost_RM_Mil":  "Daily Finance Cost (RM Mil)",
    "FFB_Processed_Tonnes":       "FFB Processed (Tonnes)",
    "Patient_Admissions":         "Patient Admissions",
    "Units_Under_Construction":   "Units Under Construction",
    "Outlet_Transactions":        "Outlet Transactions",
}


def is_favourable(metric: str, variance_pct: float) -> bool:
    higher_better = HIGHER_IS_BETTER.get(metric, True)
    return (variance_pct > 0) == higher_better


def classify_risk(variance_pct: float) -> str:
    abs_v = abs(variance_pct)
    if abs_v >= 30:
        return "Critical"
    elif abs_v >= 10:
        return "High"
    return "Low"


def get_root_cause(metric: str, variance_pct: float) -> str:
    tpl = ROOT_CAUSE_TEMPLATES.get(metric, {})
    if variance_pct >= 0:
        return tpl.get("positive", "Actual exceeded target — investigate root cause.")
    return tpl.get("negative", "Actual fell below target — investigate root cause.")


def get_action(metric: str, risk: str) -> str:
    return ACTION_TEMPLATES.get(metric, {}).get(
        risk, "Review metric trend and escalate to segment management."
    )


def seven_day_forecast_pct(domain_df: pd.DataFrame, metric: str, ref_date: pd.Timestamp) -> int:
    window = domain_df[
        (domain_df["Metric_Name"] == metric)
        & (domain_df["Date"] >= ref_date - timedelta(days=14))
        & (domain_df["Date"] < ref_date)
    ].copy()
    if window.empty:
        return 50
    window["var_pct"] = (
        (window["Actual_Value"] - window["Target_Value"]) / window["Target_Value"] * 100
    )
    anomalous_days = (window["var_pct"].abs() >= 10).sum()
    total_days = len(window)
    base_prob = round((anomalous_days / total_days) * 100)
    recent = window.tail(3)["var_pct"].abs()
    if len(recent) >= 2 and recent.iloc[-1] > recent.iloc[0]:
        base_prob = min(base_prob + 15, 99)
    return int(base_prob)


# ─────────────────────────────────────────────────────────────
# Impact Score Formula
# Combines: max variance magnitude, risk tier weight, cross-domain
# correlation bonus, and adverse direction penalty.
# Range: 0 – 100+
# ─────────────────────────────────────────────────────────────
def calc_impact_score(metrics_list: list, has_correlation: bool) -> float:
    """
    metrics_list: list of dicts with keys variance_pct, risk_score, is_favourable
    """
    if not metrics_list:
        return 0.0

    risk_weight = {"Critical": 1.0, "High": 0.5}
    total = 0.0
    for m in metrics_list:
        abs_var = abs(m["variance_pct"])
        rw = risk_weight.get(m["risk_score"], 0.2)
        # Adverse signals score higher; favourable get 0.4× weight
        direction_mult = 1.0 if not m["is_favourable"] else 0.4
        total += abs_var * rw * direction_mult

    score = total / len(metrics_list)
    # Correlation bonus: +20%
    if has_correlation:
        score *= 1.20
    # Normalise roughly into 0–100 band (variance rarely exceeds 60%)
    return round(min(score * (100 / 60), 100), 1)


# ─────────────────────────────────────────────────────────────
# Group raw alerts into Incidents
# ─────────────────────────────────────────────────────────────
def group_into_incidents(alerts: list) -> list:
    """
    Returns a list of incident dicts, each representing one grouped event.
    """
    # Index alerts by (domain, date, metric)
    alert_map: dict = {}
    for a in alerts:
        key = (a["primary_domain"], a["date_detected"], a["metric_name"])
        alert_map[key] = a

    incidents: list = []
    used_keys: set = set()

    # Walk each domain's grouping rules per date
    all_dates = sorted({a["date_detected"] for a in alerts})
    all_domains = sorted({a["primary_domain"] for a in alerts})

    for domain in all_domains:
        domain_groups = INCIDENT_GROUPS.get(domain, {})
        for date in all_dates:
            for incident_name, metric_list in domain_groups.items():
                matched = []
                for metric in metric_list:
                    k = (domain, date, metric)
                    if k in alert_map and k not in used_keys:
                        matched.append(alert_map[k])

                if not matched:
                    continue

                # Mark as used
                for a in matched:
                    used_keys.add((a["primary_domain"], a["date_detected"], a["metric_name"]))

                # Gather subsidiaries
                subsidiaries = list({a["subsidiary"] for a in matched if a.get("subsidiary")})

                # Highest severity in group
                has_critical = any(a["risk_score"] == "Critical" for a in matched)
                group_risk = "Critical" if has_critical else "High"

                # Correlation: any member carries one?
                corr_domain = next((a["correlated_domain"] for a in matched if a.get("correlated_domain")), None)
                corr_note   = next((a["correlation_note"]  for a in matched if a.get("correlation_note")),  None)

                # Favourable check: all metrics favourable?
                all_favourable = all(a["is_favourable"] for a in matched)

                # Metric summaries
                metric_summaries = []
                for a in matched:
                    metric_summaries.append({
                        "metric_name":        a["metric_name"],
                        "display_name":       METRIC_DISPLAY_NAMES.get(a["metric_name"], a["metric_name"].replace("_", " ")),
                        "target_value":       a["target_value"],
                        "actual_value":       a["actual_value"],
                        "variance_pct":       a["variance_percentage"],
                        "risk_score":         a["risk_score"],
                        "is_favourable":      a["is_favourable"],
                        "root_cause":         a["root_cause_alert"],
                        "recommended_action": a["recommended_action"],
                    })

                # Build combined narrative
                adverse = [m for m in metric_summaries if not m["is_favourable"]]
                favour  = [m for m in metric_summaries if m["is_favourable"]]
                narrative_parts = []
                if adverse:
                    names = ", ".join(m["display_name"] for m in adverse)
                    avg_var = np.mean([abs(m["variance_pct"]) for m in adverse])
                    narrative_parts.append(
                        f"Adverse deviation detected across {names} (avg {avg_var:.1f}% off target). "
                        + adverse[0]["root_cause"]
                    )
                if favour:
                    names = ", ".join(m["display_name"] for m in favour)
                    narrative_parts.append(
                        f"Positive signal in {names} — may indicate efficiency gains or demand surge."
                    )
                if corr_note:
                    narrative_parts.append(f"Cross-domain signal: {corr_note}")

                combined_narrative = " ".join(narrative_parts) or "Anomaly detected — review segment data."

                # Worst recommended action
                worst = max(matched, key=lambda a: abs(a["variance_percentage"]))
                primary_action = get_action(worst["metric_name"], worst["risk_score"])

                # 7-day forecast (max across group)
                # We use a simple heuristic: Critical=75, High=45, boosted if multiple adverse
                if group_risk == "Critical":
                    forecast_pct = 75 + min(len(adverse) * 5, 20)
                else:
                    forecast_pct = 45 + min(len(adverse) * 5, 15)
                forecast_pct = min(forecast_pct, 99)

                impact_score = calc_impact_score(
                    [{"variance_pct": m["variance_pct"], "risk_score": m["risk_score"],
                      "is_favourable": m["is_favourable"]} for m in metric_summaries],
                    has_correlation=bool(corr_domain),
                )

                incidents.append({
                    "incident_id":         f"{domain[:4].upper()}_{date.replace('-','')}_{len(incidents):02d}",
                    "incident_name":       incident_name,
                    "date_detected":       date,
                    "primary_domain":      domain,
                    "subsidiaries":        subsidiaries,
                    "risk_level":          group_risk,
                    "impact_score":        impact_score,
                    "is_all_favourable":   all_favourable,
                    "metric_count":        len(matched),
                    "metrics":             metric_summaries,
                    "combined_narrative":  combined_narrative,
                    "primary_action":      primary_action,
                    "forecast_pct":        forecast_pct,
                    "correlated_domain":   corr_domain,
                    "correlation_note":    corr_note,
                })

    # Any alert not caught by group rules → solo incident
    for a in alerts:
        k = (a["primary_domain"], a["date_detected"], a["metric_name"])
        if k not in used_keys:
            used_keys.add(k)
            metric_summaries = [{
                "metric_name":        a["metric_name"],
                "display_name":       METRIC_DISPLAY_NAMES.get(a["metric_name"], a["metric_name"].replace("_", " ")),
                "target_value":       a["target_value"],
                "actual_value":       a["actual_value"],
                "variance_pct":       a["variance_percentage"],
                "risk_score":         a["risk_score"],
                "is_favourable":      a["is_favourable"],
                "root_cause":         a["root_cause_alert"],
                "recommended_action": a["recommended_action"],
            }]
            impact_score = calc_impact_score(
                [{"variance_pct": a["variance_percentage"], "risk_score": a["risk_score"],
                  "is_favourable": a["is_favourable"]}],
                has_correlation=bool(a.get("correlated_domain")),
            )
            forecast_pct = 75 if a["risk_score"] == "Critical" else 45
            incidents.append({
                "incident_id":        f"{a['primary_domain'][:4].upper()}_{a['date_detected'].replace('-','')}_{len(incidents):02d}",
                "incident_name":      METRIC_DISPLAY_NAMES.get(a["metric_name"], a["metric_name"].replace("_", " ")) + " Incident",
                "date_detected":      a["date_detected"],
                "primary_domain":     a["primary_domain"],
                "subsidiaries":       [a["subsidiary"]] if a.get("subsidiary") else [],
                "risk_level":         a["risk_score"],
                "impact_score":       impact_score,
                "is_all_favourable":  a["is_favourable"],
                "metric_count":       1,
                "metrics":            metric_summaries,
                "combined_narrative": a["root_cause_alert"],
                "primary_action":     a["recommended_action"],
                "forecast_pct":       forecast_pct,
                "correlated_domain":  a.get("correlated_domain"),
                "correlation_note":   a.get("correlation_note"),
            })

    return incidents


# ─────────────────────────────────────────────────────────────
# Categorise incidents into the 4 executive buckets
# ─────────────────────────────────────────────────────────────
def categorise_incidents(incidents: list) -> dict:
    """
    Returns dict with keys: immediate_action, emerging_risk, monitor, opportunity
    Sorted by impact_score descending within each bucket.
    """
    # Separate opportunities (all metrics favourable) from risk signals
    opportunities = [i for i in incidents if i["is_all_favourable"]]
    risks = [i for i in incidents if not i["is_all_favourable"]]

    # Sort risks by impact_score descending
    risks.sort(key=lambda x: x["impact_score"], reverse=True)

    # Bucket split: top 3–5 = Immediate Action, next tier = Emerging Risk, rest = Monitor
    n_immediate = min(max(3, sum(1 for i in risks if i["risk_level"] == "Critical")), 5)
    immediate   = risks[:n_immediate]
    emerging    = [i for i in risks[n_immediate:] if i["risk_level"] in ("Critical", "High") and i["impact_score"] >= 15]
    monitor     = [i for i in risks[n_immediate:] if i not in emerging]

    # Sort opportunities by impact_score descending
    opportunities.sort(key=lambda x: x["impact_score"], reverse=True)

    return {
        "immediate_action": immediate,
        "emerging_risk":    emerging,
        "monitor":          monitor,
        "opportunity":      opportunities,
    }


# ─────────────────────────────────────────────────────────────
# Main pipeline
# ─────────────────────────────────────────────────────────────
def run_pipeline(csv_path: str, output_path: str) -> None:
    print(f"\n{'='*65}")
    print("  jcorp Da-iO  |  KPI Anomaly Engine v3 — Incident Intelligence")
    print(f"{'='*65}\n")

    # 1. Load & clean
    df = pd.read_csv(csv_path)
    df.dropna(inplace=True)
    df["Date"] = pd.to_datetime(df["Date"])
    df.sort_values("Date", inplace=True)
    df.reset_index(drop=True, inplace=True)
    print(f"[Load]  {len(df)} rows loaded from '{csv_path}'.")

    # 2. Variance & risk
    df["Variance_Pct"] = (
        (df["Actual_Value"] - df["Target_Value"]) / df["Target_Value"] * 100
    ).round(2)
    df["Risk_Score"] = df["Variance_Pct"].apply(classify_risk)

    # 3. Funnel — step 1: total signals
    total_signals = len(df)

    # 4. Suppress Low risk
    df_filtered = df[df["Risk_Score"] != "Low"].copy()
    suppressed_count = total_signals - len(df_filtered)
    retained_count   = len(df_filtered)
    print(f"[Filter] Suppressed {suppressed_count} Low-risk rows. {retained_count} retained.")

    output_dir = os.path.dirname(output_path) if os.path.dirname(output_path) else "."

    if df_filtered.empty:
        print("[Warning] No Critical or High alerts found.")
        os.makedirs(output_dir, exist_ok=True)
        with open(output_path, "w") as f:
            json.dump({
                "funnel_metrics": {
                    "total_signals": total_signals,
                    "suppressed":    suppressed_count,
                    "retained":      0,
                    "escalated":     0,
                },
                "categorized_incidents": {
                    "immediate_action": [],
                    "emerging_risk":    [],
                    "monitor":          [],
                    "opportunity":      [],
                },
            }, f, indent=2)
        return

    # 5. Build flat alert list (pre-grouping)
    alerts = []
    for _, row in df_filtered.iterrows():
        subsidiary = row.get("Subsidiary", "") if "Subsidiary" in df_filtered.columns else ""
        var_pct = float(row["Variance_Pct"])
        risk    = row["Risk_Score"]
        metric  = row["Metric_Name"]
        alerts.append({
            "date_detected":     row["Date"].strftime("%Y-%m-%d"),
            "primary_domain":    row["Domain"],
            "subsidiary":        subsidiary,
            "metric_name":       metric,
            "target_value":      round(float(row["Target_Value"]), 2),
            "actual_value":      round(float(row["Actual_Value"]), 2),
            "variance_percentage": round(var_pct, 2),
            "risk_score":        risk,
            "root_cause_alert":  get_root_cause(metric, var_pct),
            "recommended_action": get_action(metric, risk),
            "correlated_domain": None,
            "correlation_note":  None,
            "is_favourable":     is_favourable(metric, var_pct),
        })

    # 6. Cross-domain correlation (Critical-to-Critical, ±1 day)
    critical_domain_dates: dict = {}
    for a in alerts:
        if a["risk_score"] == "Critical":
            critical_domain_dates.setdefault(a["primary_domain"], set()).add(
                pd.Timestamp(a["date_detected"])
            )

    correlated = 0
    for alert in alerts:
        ref          = pd.Timestamp(alert["date_detected"])
        window_dates = {ref - timedelta(days=1), ref, ref + timedelta(days=1)}
        domain_a     = alert["primary_domain"]
        if alert["risk_score"] != "Critical":
            continue
        for (pair_x, pair_y, note_x, note_y) in CORRELATION_PAIRS:
            if domain_a == pair_x:
                if window_dates & critical_domain_dates.get(pair_y, set()):
                    alert["correlated_domain"] = pair_y
                    alert["correlation_note"]  = note_x
                    correlated += 1
                    break
            elif domain_a == pair_y:
                if window_dates & critical_domain_dates.get(pair_x, set()):
                    alert["correlated_domain"] = pair_x
                    alert["correlation_note"]  = note_y
                    correlated += 1
                    break

    print(f"[Correlate] {correlated} cross-domain correlations identified.")

    # 7. Group into incidents
    incidents = group_into_incidents(alerts)
    print(f"[Group] {len(alerts)} alerts -> {len(incidents)} incidents.")

    # 8. Categorise
    categorised = categorise_incidents(incidents)
    escalated_count = len(categorised["immediate_action"])

    # 9. Funnel metrics
    funnel_metrics = {
        "total_signals": total_signals,
        "suppressed":    suppressed_count,
        "retained":      retained_count,
        "escalated":     escalated_count,
    }

    # 10. Write output
    os.makedirs(output_dir, exist_ok=True)
    output = {
        "funnel_metrics":        funnel_metrics,
        "categorized_incidents": {
            "immediate_action": categorised["immediate_action"],
            "emerging_risk":    categorised["emerging_risk"],
            "monitor":          categorised["monitor"],
            "opportunity":      categorised["opportunity"],
        },
    }

    with open(output_path, "w", encoding="utf-8") as f:
        json.dump(output, f, indent=2, ensure_ascii=False)

    # Summary
    print(f"\n{'='*65}")
    print(f"  Output: '{output_path}'")
    print(f"\n  Signal Funnel:")
    print(f"    {total_signals} signals analysed")
    print(f"    -> {suppressed_count} low-priority signals suppressed")
    print(f"    -> {retained_count} retained")
    print(f"    -> {escalated_count} escalated for executive action")
    print(f"\n  Incident Buckets:")
    print(f"    Immediate Action : {len(categorised['immediate_action'])}")
    print(f"    Emerging Risk    : {len(categorised['emerging_risk'])}")
    print(f"    Monitor          : {len(categorised['monitor'])}")
    print(f"    Opportunity      : {len(categorised['opportunity'])}")
    print(f"{'='*65}\n")


# ─────────────────────────────────────────────────────────────
if __name__ == "__main__":
    BASE_DIR = os.path.dirname(os.path.abspath(__file__))
    run_pipeline(
        csv_path=os.path.join(BASE_DIR, "sample_kpi_data.csv"),
        output_path=os.path.join(BASE_DIR, "anomalies_alerts.json"),
    )
