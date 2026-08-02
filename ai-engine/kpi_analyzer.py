import pandas as pd
import numpy as np
import json
import os
from datetime import timedelta

# ─────────────────────────────────────────────────────────────
# Root-cause & recommendation templates per metric
# ─────────────────────────────────────────────────────────────
ROOT_CAUSE_TEMPLATES = {
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
    "Revenue": {
        "Critical": "Escalate to CFO immediately. Conduct emergency revenue audit and review top-3 accounts.",
        "High": "Schedule finance review within 24 hrs. Assess sales pipeline and adjust Q-forecast.",
    },
    "Units_Produced": {
        "Critical": "Halt affected production line. Engage maintenance team and notify supply chain manager.",
        "High": "Increase monitoring frequency on production floor. Review shift logs and material inventory.",
    },
}


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
    Uses: % of those days that were also anomalous (|variance| >= 10%)
    scaled to 0-100%.
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
    before = len(df)
    df = df[df["Risk_Score"] != "Low"].copy()
    print(f"[Filter] Dropped {before - len(df)} Low-risk rows. {len(df)} alerts remaining.")

    if df.empty:
        print("[Warning] No Critical or High alerts found. Writing empty JSON.")
        with open(output_path, "w") as f:
            json.dump([], f, indent=2)
        return

    # 4. Build base alert list
    alerts = []
    for _, row in df.iterrows():
        alert = {
            "date_detected": row["Date"].strftime("%Y-%m-%d"),
            "primary_domain": row["Domain"],
            "metric_name": row["Metric_Name"],
            "variance_percentage": round(float(row["Variance_Pct"]), 2),
            "risk_score": row["Risk_Score"],
            "root_cause_alert": get_root_cause(row["Metric_Name"], row["Variance_Pct"]),
            "7_day_forecast": seven_day_forecast(df, row["Metric_Name"], row["Date"]),
            "recommended_action": get_action(row["Metric_Name"], row["Risk_Score"]),
            "correlated_domain": None,
            "correlation_note": None,
        }
        alerts.append(alert)

    # 5. Cross-domain correlation (±1 day window)
    fin_dates = {
        pd.Timestamp(a["date_detected"])
        for a in alerts if a["primary_domain"] == "Finance"
    }
    ops_dates = {
        pd.Timestamp(a["date_detected"])
        for a in alerts if a["primary_domain"] == "Operations"
    }

    correlated = 0
    for alert in alerts:
        ref = pd.Timestamp(alert["date_detected"])
        window = {ref - timedelta(days=1), ref, ref + timedelta(days=1)}

        if alert["primary_domain"] == "Finance" and window & ops_dates:
            alert["correlated_domain"] = "Operations"
            alert["correlation_note"] = (
                "Operational disruption detected within ±1 day — "
                "production shortfall likely contributed to this financial variance."
            )
            correlated += 1
        elif alert["primary_domain"] == "Operations" and window & fin_dates:
            alert["correlated_domain"] = "Finance"
            alert["correlation_note"] = (
                "Financial anomaly detected within ±1 day — "
                "this operational deviation may have triggered downstream revenue impact."
            )
            correlated += 1

    print(f"[Correlate] {correlated} cross-domain correlations identified.")

    # 6. Sort Critical → High
    rank = {"Critical": 0, "High": 1}
    alerts.sort(key=lambda a: rank.get(a["risk_score"], 2))

    # 7. Save JSON
    os.makedirs(os.path.dirname(output_path) if os.path.dirname(output_path) else ".", exist_ok=True)
    with open(output_path, "w") as f:
        json.dump(alerts, f, indent=2)

    critical_n = sum(1 for a in alerts if a["risk_score"] == "Critical")
    high_n = sum(1 for a in alerts if a["risk_score"] == "High")
    corr_n = sum(1 for a in alerts if a["correlated_domain"] is not None)

    print(f"\n{'='*65}")
    print(f"  Output saved -> '{output_path}'")
    print(f"  Total alerts : {len(alerts)}  |  Critical: {critical_n}  |  High: {high_n}")
    print(f"  Cross-domain correlations: {corr_n}")
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
