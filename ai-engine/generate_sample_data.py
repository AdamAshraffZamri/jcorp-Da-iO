"""
JCorp Da-iO  –  Synthetic KPI Data Generator
=============================================
Based on Johor Corporation Annual Financial Statements 2025.

Segment revenue (RM million, 2025):
  Agribusiness                : 1,758
  Wellness and healthcare     : 4,241
  Real estate and infrastructure: 1,368
  Others (QSR / KFC / PH)    :   259
  ---
  Group total                 : 7,626

Finance costs by segment (RM million, 2025):
  Agribusiness                :    60
  Wellness and healthcare     :   188
  Real estate and infrastructure:  135
  Others                      :   170
  ---
  Group total                 :   553

Segment gross profit proxy (RM million, 2025):
  Group gross profit = 3,074 on revenue 7,626  => ~40.3% GP margin
  Applied per-segment at same 40.3% rate
  (Real estate GP margin known lower; Others higher due to QSR franchise mix)

Generated period: 30 calendar days (2025-01-01 to 2025-01-30)
Daily baselines derived from annual figures / 365.
"""

import pandas as pd
import numpy as np
from datetime import date, timedelta
import os

np.random.seed(2025)

# ---------------------------------------------------------------------------
# 1. Domain / Subsidiary mapping
# ---------------------------------------------------------------------------
DOMAIN_CONFIG = {
    "Agribusiness": {
        "subsidiaries": ["Kulim (Malaysia) Berhad", "Johor Plantation Group"],
        # Annual revenue: RM 1,758M => daily = 1758/365
        "daily_revenue_target":   round(1758e6 / 365 / 1e6, 4),   # RM million
        # Annual gross profit: ~40.3% of revenue
        "daily_gp_target":        round(1758e6 * 0.403 / 365 / 1e6, 4),
        # Annual finance costs: RM 60M
        "daily_fc_target":        round(60e6 / 365 / 1e6, 4),
        # Daily operational units (palm fresh fruit bunches, tonnes)
        "daily_ops_target":       4500,
        "ops_metric":             "FFB_Processed_Tonnes",
        "ops_noise_pct":          0.07,
        "rev_noise_pct":          0.08,
        "gp_noise_pct":           0.09,
        "fc_noise_pct":           0.06,
    },
    "Wellness and healthcare": {
        "subsidiaries": ["KPJ Healthcare Berhad", "KPJ Johor Specialist Hospital"],
        # Annual revenue: RM 4,241M
        "daily_revenue_target":   round(4241e6 / 365 / 1e6, 4),
        # GP margin for healthcare ~46% (services-heavy)
        "daily_gp_target":        round(4241e6 * 0.46 / 365 / 1e6, 4),
        # Finance costs: RM 188M
        "daily_fc_target":        round(188e6 / 365 / 1e6, 4),
        # Patient admissions per day
        "daily_ops_target":       1200,
        "ops_metric":             "Patient_Admissions",
        "ops_noise_pct":          0.06,
        "rev_noise_pct":          0.05,
        "gp_noise_pct":           0.06,
        "fc_noise_pct":           0.05,
    },
    "Real estate and infrastructure": {
        "subsidiaries": ["JCorp Properties Sdn Bhd", "Damansara Assets Sdn Bhd", "JCIT"],
        # Annual revenue: RM 1,368M
        "daily_revenue_target":   round(1368e6 / 365 / 1e6, 4),
        # GP margin for real estate ~30% (property development mix)
        "daily_gp_target":        round(1368e6 * 0.30 / 365 / 1e6, 4),
        # Finance costs: RM 135M (highest leverage segment)
        "daily_fc_target":        round(135e6 / 365 / 1e6, 4),
        # Units under construction / project milestones
        "daily_ops_target":       85,
        "ops_metric":             "Units_Under_Construction",
        "ops_noise_pct":          0.05,
        "rev_noise_pct":          0.10,
        "gp_noise_pct":           0.12,
        "fc_noise_pct":           0.08,
    },
    "Others": {
        "subsidiaries": ["QSR Brands (M) Holdings Bhd", "Sindora Berhad", "Johor Port Sdn Bhd"],
        # Annual revenue: RM 259M
        "daily_revenue_target":   round(259e6 / 365 / 1e6, 4),
        # GP margin ~50% (QSR franchise / F&B mix)
        "daily_gp_target":        round(259e6 * 0.50 / 365 / 1e6, 4),
        # Finance costs: RM 170M
        "daily_fc_target":        round(170e6 / 365 / 1e6, 4),
        # Daily outlet transactions
        "daily_ops_target":       3200,
        "ops_metric":             "Outlet_Transactions",
        "ops_noise_pct":          0.09,
        "rev_noise_pct":          0.07,
        "gp_noise_pct":           0.08,
        "fc_noise_pct":           0.07,
    },
}

# ---------------------------------------------------------------------------
# 2. Build base 30-day dataset  (4 domains × 4 metrics × 30 days = 480 rows)
# ---------------------------------------------------------------------------
START_DATE = date(2025, 1, 1)
NUM_DAYS   = 30
dates_all  = [START_DATE + timedelta(days=d) for d in range(NUM_DAYS)]

records = []

for domain, cfg in DOMAIN_CONFIG.items():
    subsidiaries = cfg["subsidiaries"]

    for d_idx, day in enumerate(dates_all):
        # Assign a subsidiary (rotate through list, slight random variation)
        subsidiary = subsidiaries[d_idx % len(subsidiaries)]

        # ── Daily Revenue ──────────────────────────────────────────────────
        rev_target = round(cfg["daily_revenue_target"] * np.random.normal(1.0, 0.03), 4)
        rev_actual = round(rev_target * np.random.normal(1.0, cfg["rev_noise_pct"]), 4)
        records.append({
            "Date":       day,
            "Domain":     domain,
            "Subsidiary": subsidiary,
            "Metric_Name": "Daily_Revenue_RM_Mil",
            "Target_Value": rev_target,
            "Actual_Value": rev_actual,
        })

        # ── Gross Profit ───────────────────────────────────────────────────
        gp_target = round(cfg["daily_gp_target"] * np.random.normal(1.0, 0.03), 4)
        gp_actual = round(gp_target * np.random.normal(1.0, cfg["gp_noise_pct"]), 4)
        records.append({
            "Date":       day,
            "Domain":     domain,
            "Subsidiary": subsidiary,
            "Metric_Name": "Daily_Gross_Profit_RM_Mil",
            "Target_Value": gp_target,
            "Actual_Value": gp_actual,
        })

        # ── Finance Costs ─────────────────────────────────────────────────
        fc_target = round(cfg["daily_fc_target"] * np.random.normal(1.0, 0.02), 4)
        fc_actual = round(fc_target * np.random.normal(1.0, cfg["fc_noise_pct"]), 4)
        records.append({
            "Date":       day,
            "Domain":     domain,
            "Subsidiary": subsidiary,
            "Metric_Name": "Daily_Finance_Cost_RM_Mil",
            "Target_Value": fc_target,
            "Actual_Value": fc_actual,
        })

        # ── Operational Metric ─────────────────────────────────────────────
        ops_target = round(cfg["daily_ops_target"] * np.random.normal(1.0, 0.03))
        ops_actual = round(ops_target * np.random.normal(1.0, cfg["ops_noise_pct"]))
        records.append({
            "Date":       day,
            "Domain":     domain,
            "Subsidiary": subsidiary,
            "Metric_Name": cfg["ops_metric"],
            "Target_Value": ops_target,
            "Actual_Value": ops_actual,
        })

df = pd.DataFrame(records)
df["Date"] = pd.to_datetime(df["Date"])

# ---------------------------------------------------------------------------
# 3. Inject deliberate CRITICAL anomalies (guaranteed catches for the system)
# ---------------------------------------------------------------------------
#
# ANOMALY A  –  Agribusiness / Real estate CORRELATION trigger
#   Day 8 (2025-01-08): Agribusiness revenue crashes -42%
#                       Real estate revenue also dips -25% (supply chain link)
#
# ANOMALY B  –  Real estate Finance Cost spike +55%  (Day 12)
#   Represents sudden drawdown on project financing
#
# ANOMALY C  –  Wellness & healthcare patient admissions collapse -38%  (Day 18)
#   IT outage / booking system failure scenario
#
# ANOMALY D  –  Others (QSR) revenue surge +48%  (Day 22)  +  Finance cost +35%
#   Franchise expansion cost overrun
#
# ANOMALY E  –  Agribusiness FFB_Processed_Tonnes crash -44%  (Day 26)
#   Machinery breakdown / flood event

def inject(df, day_offset, domain, metric, actual_multiplier):
    """Set Actual_Value for one specific row."""
    target_date = pd.Timestamp(START_DATE + timedelta(days=day_offset))
    mask = (df["Date"] == target_date) & (df["Domain"] == domain) & (df["Metric_Name"] == metric)
    if mask.sum() == 1:
        df.loc[mask, "Actual_Value"] = round(
            df.loc[mask, "Target_Value"].values[0] * actual_multiplier, 4
        )
    return df

# Anomaly A
df = inject(df,  7, "Agribusiness",                  "Daily_Revenue_RM_Mil",      0.58)   # -42%
df = inject(df,  7, "Real estate and infrastructure", "Daily_Revenue_RM_Mil",      0.62)   # -38%  (Critical: triggers Agribusiness correlation)

# Anomaly B
df = inject(df, 11, "Real estate and infrastructure", "Daily_Finance_Cost_RM_Mil", 1.55)   # +55%

# Anomaly C
df = inject(df, 17, "Wellness and healthcare",        "Patient_Admissions",         0.62)   # -38%
df = inject(df, 17, "Wellness and healthcare",        "Daily_Revenue_RM_Mil",       0.70)   # -30% (revenue follows admissions)

# Anomaly D  (Day 18 Others = same day as Wellness anomaly C, triggers correlation)
df = inject(df, 17, "Others",                         "Outlet_Transactions",        0.62)   # -38%  correlates with Wellness admission collapse
df = inject(df, 21, "Others",                         "Daily_Revenue_RM_Mil",      1.48)   # +48%
df = inject(df, 21, "Others",                         "Daily_Finance_Cost_RM_Mil", 1.35)   # +35%

# Anomaly E
df = inject(df, 25, "Agribusiness",                  "FFB_Processed_Tonnes",      0.56)   # -44%
df = inject(df, 25, "Agribusiness",                  "Daily_Gross_Profit_RM_Mil", 0.65)   # -35% cascades to GP

# ---------------------------------------------------------------------------
# 4. Save output
# ---------------------------------------------------------------------------
output_path = os.path.join(os.path.dirname(__file__), "sample_kpi_data.csv")
df_out = df[["Date", "Domain", "Subsidiary", "Metric_Name", "Target_Value", "Actual_Value"]].copy()
df_out["Date"] = df_out["Date"].dt.strftime("%Y-%m-%d")
df_out.to_csv(output_path, index=False)

print(f"Dataset saved to '{output_path}'  ({len(df_out)} rows across {NUM_DAYS} days, 4 domains, 4 metrics)")
print()
print("Injected anomalies summary:")
anomalies = [
    ("2025-01-08", "Agribusiness",                  "Daily_Revenue_RM_Mil",      "CRITICAL  -42% revenue crash"),
    ("2025-01-08", "Real estate and infrastructure", "Daily_Revenue_RM_Mil",      "CRITICAL  -25% correlated revenue drop"),
    ("2025-01-12", "Real estate and infrastructure", "Daily_Finance_Cost_RM_Mil", "CRITICAL  +55% finance cost spike"),
    ("2025-01-18", "Wellness and healthcare",        "Patient_Admissions",         "CRITICAL  -38% admissions collapse"),
    ("2025-01-18", "Wellness and healthcare",        "Daily_Revenue_RM_Mil",       "HIGH      -30% revenue impact"),
    ("2025-01-22", "Others",                         "Daily_Revenue_RM_Mil",       "HIGH      +48% QSR revenue surge"),
    ("2025-01-22", "Others",                         "Daily_Finance_Cost_RM_Mil",  "HIGH      +35% cost overrun"),
    ("2025-01-26", "Agribusiness",                  "FFB_Processed_Tonnes",       "CRITICAL  -44% production crash"),
    ("2025-01-26", "Agribusiness",                  "Daily_Gross_Profit_RM_Mil",  "HIGH      -35% GP cascade"),
]
print(f"  {'Date':<14} {'Domain':<32} {'Metric':<30} Severity")
print(f"  {'-'*14} {'-'*32} {'-'*30} {'-'*40}")
for row in anomalies:
    print(f"  {row[0]:<14} {row[1]:<32} {row[2]:<30} {row[3]}")
print()
print(f"Variance preview:")
df_check = df_out.copy()
df_check["Variance_Pct"] = ((df_check["Actual_Value"].astype(float) - df_check["Target_Value"].astype(float)) / df_check["Target_Value"].astype(float) * 100).round(1)
critical_rows = df_check[df_check["Variance_Pct"].abs() >= 30]
print(critical_rows[["Date","Domain","Metric_Name","Variance_Pct"]].to_string(index=False))
