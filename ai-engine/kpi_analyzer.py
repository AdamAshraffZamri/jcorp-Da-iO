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
    # Legacy fallbacks
    "Revenue": {
        "positive": "Revenue exceeded target — possible demand surge or pricing uplift.",
        "negative": "Revenue fell below target — potential demand drop, churn, or pricing pressure.",
    },
    "Units_Produced": {
        "positive": "Production exceeded target — possible inventory build-up or equipment over-run.",
        "negative": "Production fell below target — likely supply chain disruption or equipment downtime.",
    },
}

ACTION_TEMPLATES = {
    "Daily_Revenue_RM_Mil": {
        "Critical": "Escalate to Group CFO and segment CEO immediately. Initiate emergency revenue audit, review top-5 contracts, and assess impact on Group annual guidance.",
        "High": "Schedule segment finance review within 24 hrs. Reassess quarterly sales forecast and identify revenue recovery levers.",
    },
    "Daily_Gross_Profit_RM_Mil": {
        "Critical": "Convene emergency cost-review committee. Engage procurement on input cost hedging and review pricing strategy with commercial team.",
        "High": "Review cost-of-sales breakdown. Identify top-3 margin leakage sources and submit remediation plan within 48 hrs.",
    },
    "Daily_Finance_Cost_RM_Mil": {
        "Critical": "Alert Group Treasurer and Board Finance Committee. Review all active credit facilities, identify unplanned drawdowns, and assess interest rate exposure.",
        "High": "Review loan utilisation report. Confirm all drawdowns are approved and evaluate refinancing options to reduce cost.",
    },
    "FFB_Processed_Tonnes": {
        "Critical": "Deploy emergency maintenance team to affected mills. Notify Group Operations Director and activate business continuity plan. Assess crop loss impact.",
        "High": "Increase mill monitoring frequency. Review equipment service logs and assess estate-level yield data for harvest disruptions.",
    },
    "Patient_Admissions": {
        "Critical": "Escalate to KPJ Healthcare CEO. Investigate booking system and IT infrastructure. Activate patient diversion protocol and notify Ministry of Health if required.",
        "High": "Review admissions data by hospital unit. Identify specific wards/services affected and engage clinical operations team.",
    },
    "Units_Under_Construction": {
        "Critical": "Alert project directors and legal team. Review contractor performance bonds, assess regulatory compliance, and revise delivery timeline.",
        "High": "Conduct site progress review. Identify bottlenecks in materials procurement and sub-contractor performance.",
    },
    "Outlet_Transactions": {
        "Critical": "Escalate to QSR Brands CEO. Review outlet-level POS data, identify affected locations, and launch targeted promotional response.",
        "High": "Analyse transaction data by outlet cluster. Review stock availability and escalate to franchise operations team.",
    },
    # Legacy fallbacks
    "Revenue": {
        "Critical": "Escalate to CFO immediately. Conduct emergency revenue audit and review top-3 accounts.",
        "High": "Schedule finance review within 24 hrs. Assess sales pipeline and adjust Q-forecast.",
    },
    "Units_Produced": {
        "Critical": "Halt affected production line. Engage maintenance team and notify supply chain manager.",
        "High": "Increase monitoring frequency on production floor. Review shift logs and material inventory.",
    },
}

# ─────────────────────────────────────────────────────────────
# Cross-domain correlation pairs for JCorp segments
# ─────────────────────────────────────────────────────────────
# Key insight from AFS 2025:
#   Agribusiness <-> Real estate and infrastructure
#     (Plantation land bank feeds property pipeline; CPO price impacts Group cashflow)
#   Wellness and healthcare <-> Others
#     (QSR/Others finance costs share treasury pool with KPJ)
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
# Metric direction — is a HIGHER actual value good or bad?
# Used to flag "Opportunity" (favourable) anomalies vs true risks.
# ─────────────────────────────────────────────────────────────
HIGHER_IS_BETTER = {
    "Daily_Revenue_RM_Mil": True,
    "Daily_Gross_Profit_RM_Mil": True,
    "Daily_Finance_Cost_RM_Mil": False,   # lower finance cost is better
    "FFB_Processed_Tonnes": True,
    "Patient_Admissions": True,
    "Units_Under_Construction": True,
    "Outlet_Transactions": True,
    "Revenue": True,
    "Units_Produced": True,
}


def is_favourable(metric: str, variance_pct: float) -> bool:
    """True when the deviation is actually GOOD news for the business."""
    higher_better = HIGHER_IS_BETTER.get(metric, True)
    return (variance_pct > 0) == higher_better


def classify_risk(variance_pct: float) -> str:
    """Critical if |variance| >= 30%, High if >= 10%, else Low."""
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
        risk, "Review metric trend and escalate as needed."
    )


def seven_day_forecast(domain_df: pd.DataFrame, metric: str, ref_date: pd.Timestamp) -> str:
    """
    Estimate 7-day recurrence probability from the variance trajectory
    of the last 14 days before ref_date for the given metric.
    """
    window = domain_df[
        (domain_df["Metric_Name"] == metric)
        & (domain_df["Date"] >= ref_date - timedelta(days=14))
        & (domain_df["Date"] < ref_date)
    ].copy()

    if window.empty:
        return "50% probability of recurring failure"

    window["var_pct"] = (
        (window["Actual_Value"] - window["Target_Value"]) / window["Target_Value"] * 100
    )
    anomalous_days = (window["var_pct"].abs() >= 10).sum()
    total_days = len(window)
    base_prob = round((anomalous_days / total_days) * 100)

    # Trend boost: if last 3 readings are worsening, add 15 pp
    recent = window.tail(3)["var_pct"].abs()
    if len(recent) >= 2 and recent.iloc[-1] > recent.iloc[0]:
        base_prob = min(base_prob + 15, 99)

    return f"{base_prob}% probability of recurring failure"


# ─────────────────────────────────────────────────────────────
# Main pipeline
# ─────────────────────────────────────────────────────────────
def run_pipeline(csv_path: str, output_path: str) -> None:
    print(f"\n{'='*65}")
    print("  jcorp Da-iO  |  KPI Anomaly Engine")
    print(f"{'='*65}\n")

    # 1. Load & clean
    df = pd.read_csv(csv_path)
    df.dropna(inplace=True)
    df["Date"] = pd.to_datetime(df["Date"])
    df.sort_values("Date", inplace=True)
    df.reset_index(drop=True, inplace=True)
    print(f"[Load]  {len(df)} rows loaded from '{csv_path}'.")

    # 2. Calculate variance % and risk score
    df["Variance_Pct"] = (
        (df["Actual_Value"] - df["Target_Value"]) / df["Target_Value"] * 100
    ).round(2)
    df["Risk_Score"] = df["Variance_Pct"].apply(classify_risk)

    # 3. Zero-fatigue filtering — drop Low risk
    total_signals = len(df)
    df = df[df["Risk_Score"] != "Low"].copy()
    suppressed_count = total_signals - len(df)
    print(f"[Filter] Dropped {suppressed_count} Low-risk rows. {len(df)} alerts remaining.")

    output_dir = os.path.dirname(output_path) if os.path.dirname(output_path) else "."
    funnel_path = os.path.join(output_dir, "signal_funnel.json")

    if df.empty:
        print("[Warning] No Critical or High alerts found. Writing empty JSON.")
        os.makedirs(output_dir, exist_ok=True)
        with open(output_path, "w") as f:
            json.dump([], f, indent=2)
        with open(funnel_path, "w") as f:
            json.dump({
                "total_signals": total_signals,
                "suppressed_count": suppressed_count,
                "retained_count": 0,
            }, f, indent=2)
        return

    # 4. Build base alert list
    alerts = []
    for _, row in df.iterrows():
        # Determine subsidiary if column exists
        subsidiary = row.get("Subsidiary", "") if "Subsidiary" in df.columns else ""
        alert = {
            "date_detected": row["Date"].strftime("%Y-%m-%d"),
            "primary_domain": row["Domain"],
            "subsidiary": subsidiary,
            "metric_name": row["Metric_Name"],
            "target_value": round(float(row["Target_Value"]), 2),
            "actual_value": round(float(row["Actual_Value"]), 2),
            "variance_percentage": round(float(row["Variance_Pct"]), 2),
            "risk_score": row["Risk_Score"],
            "root_cause_alert": get_root_cause(row["Metric_Name"], row["Variance_Pct"]),
            "7_day_forecast": seven_day_forecast(df, row["Metric_Name"], row["Date"]),
            "recommended_action": get_action(row["Metric_Name"], row["Risk_Score"]),
            "correlated_domain": None,
            "correlation_note": None,
            "is_favourable": is_favourable(row["Metric_Name"], row["Variance_Pct"]),
        }
        alerts.append(alert)

    # 5. Cross-domain correlation (±1 day window) — JCorp segment pairs
    # Only correlate CRITICAL-severity alerts to reduce noise; a Critical alert
    # in one domain triggers correlation check against any alert (Critical or High)
    # in the paired domain within ±1 day.
    critical_domain_dates: dict = {}
    for a in alerts:
        if a["risk_score"] == "Critical":
            d = a["primary_domain"]
            critical_domain_dates.setdefault(d, set()).add(pd.Timestamp(a["date_detected"]))

    # All-alert date sets (Critical + High) for partner matching
    all_domain_dates: dict = {}
    for a in alerts:
        d = a["primary_domain"]
        all_domain_dates.setdefault(d, set()).add(pd.Timestamp(a["date_detected"]))

    correlated = 0
    for alert in alerts:
        ref = pd.Timestamp(alert["date_detected"])
        window_dates = {ref - timedelta(days=1), ref, ref + timedelta(days=1)}
        domain_a = alert["primary_domain"]

        for (pair_x, pair_y, note_x, note_y) in CORRELATION_PAIRS:
            # Only correlate if THIS alert is Critical AND the partner domain
            # has at least one Critical alert within the ±1-day window
            if domain_a == pair_x and alert["risk_score"] == "Critical":
                partner_critical = critical_domain_dates.get(pair_y, set())
                if window_dates & partner_critical:
                    alert["correlated_domain"] = pair_y
                    alert["correlation_note"] = note_x
                    correlated += 1
                    break
            elif domain_a == pair_y and alert["risk_score"] == "Critical":
                partner_critical = critical_domain_dates.get(pair_x, set())
                if window_dates & partner_critical:
                    alert["correlated_domain"] = pair_x
                    alert["correlation_note"] = note_y
                    correlated += 1
                    break

    print(f"[Correlate] {correlated} cross-domain correlations identified.")

    # 6. Sort Critical -> High, then by date
    rank = {"Critical": 0, "High": 1}
    alerts.sort(key=lambda a: (rank.get(a["risk_score"], 2), a["date_detected"]))

    # 7. Save JSON
    os.makedirs(output_dir, exist_ok=True)
    with open(output_path, "w", encoding="utf-8") as f:
        json.dump(alerts, f, indent=2, ensure_ascii=False)

    critical_n = sum(1 for a in alerts if a["risk_score"] == "Critical")
    high_n = sum(1 for a in alerts if a["risk_score"] == "High")
    corr_n = sum(1 for a in alerts if a["correlated_domain"] is not None)
    favourable_n = sum(1 for a in alerts if a["is_favourable"])

    # 8. Save signal funnel stats — powers the "Zero-Fatigue" funnel widget
    with open(funnel_path, "w", encoding="utf-8") as f:
        json.dump({
            "total_signals": total_signals,
            "suppressed_count": suppressed_count,
            "retained_count": len(alerts),
        }, f, indent=2)

    print(f"\n{'='*65}")
    print(f"  Output saved -> '{output_path}'")
    print(f"  Total signals: {total_signals}  |  Suppressed (Low): {suppressed_count}  |  Retained: {len(alerts)}")
    print(f"  Critical: {critical_n}  |  High: {high_n}  |  Favourable/Opportunity: {favourable_n}")
    print(f"  Cross-domain correlations: {corr_n}")
    print(f"  Funnel stats saved -> '{funnel_path}'")
    print(f"{'='*65}\n")


# ─────────────────────────────────────────────────────────────
# Entry point
# ─────────────────────────────────────────────────────────────
if __name__ == "__main__":
    BASE_DIR = os.path.dirname(os.path.abspath(__file__))
    run_pipeline(
        csv_path=os.path.join(BASE_DIR, "sample_kpi_data.csv"),
        output_path=os.path.join(BASE_DIR, "anomalies_alerts.json"),
    )
