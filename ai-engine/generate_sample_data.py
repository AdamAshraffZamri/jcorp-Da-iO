import pandas as pd
import numpy as np
from datetime import date, timedelta

np.random.seed(42)

NUM_ROWS = 100
OUTLIER_INDICES = [10, 25, 47, 63, 81]  # positions where outliers are injected

METRIC_CONFIG = {
    "Finance": {
        "metric_name": "Revenue",
        "target_mean": 50000,
        "target_std": 5000,
        "noise_std": 4000,
    },
    "Operations": {
        "metric_name": "Units_Produced",
        "target_mean": 1200,
        "target_std": 100,
        "noise_std": 80,
    },
}

start_date = date(2026, 1, 1)
dates = [start_date + timedelta(days=i) for i in range(NUM_ROWS)]

domains = np.random.choice(["Finance", "Operations"], size=NUM_ROWS)

metric_names = []
target_values = []
actual_values = []

for i, domain in enumerate(domains):
    cfg = METRIC_CONFIG[domain]

    target = round(np.random.normal(cfg["target_mean"], cfg["target_std"]), 2)
    actual = round(target + np.random.normal(0, cfg["noise_std"]), 2)

    # Inject outliers: multiply actual by a large factor (±3-5×)
    if i in OUTLIER_INDICES:
        multiplier = np.random.choice([-1, 1]) * np.random.uniform(3.5, 5.5)
        actual = round(target * multiplier, 2)

    metric_names.append(cfg["metric_name"])
    target_values.append(target)
    actual_values.append(actual)

df = pd.DataFrame(
    {
        "Date": dates,
        "Domain": domains,
        "Metric_Name": metric_names,
        "Target_Value": target_values,
        "Actual_Value": actual_values,
    }
)

output_path = "sample_kpi_data.csv"
df.to_csv(output_path, index=False)

print(f"Dataset saved to '{output_path}' ({len(df)} rows).")
print(f"\nOutlier rows (indices {OUTLIER_INDICES}):")
print(df.iloc[OUTLIER_INDICES][["Date", "Domain", "Metric_Name", "Target_Value", "Actual_Value"]].to_string(index=True))
