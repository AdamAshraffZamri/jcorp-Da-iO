# JCorp Early Warning Radar - Anomaly Report

**Source:** `ai-engine/sample_kpi_data.csv` (100 daily KPI rows, 2026-01-01 to 2026-04-10)
**Engine:** JCorp Early Warning Radar v1 (variance + risk scoring + root-cause phrasing)

## Executive Summary

| Risk | Count |
|------|-------|
| **Critical** | 5 |
| **High** | 17 |
| Low | 78 |

All 5 Critical hits are statistical outliers whose absolute value deviates from target by more than 25%. They are concentrated in the Finance/Revenue stream (3 of 5) and the Operations/Units_Produced stream (2 of 5). Treat them as data-integrity incidents until proven otherwise.

## Critical Anomalies (War-Room Within 24h)

### 2026-01-11 - Finance / Revenue [CRITICAL]
- **Target:** 47,604.13  |  **Actual:** -230,293.67  |  **Variance:** -583.77%
- **Root cause:** Revenue underperformed in Finance: actual -230293.67 missed target 47604.13 severely (-583.77%).
- **Action:** Convene a Finance war-room within 24h; audit pipeline, pricing, and top-account churn drivers.

### 2026-01-26 - Operations / Units_Produced [CRITICAL]
- **Target:** 1,296.26  |  **Actual:** 6,630.21  |  **Variance:** +411.49%
- **Root cause:** Units_Produced in Operations exceeded target 1296.26 severely (411.49%) - favorable but verify driver.
- **Action:** Validate the Units_Produced upside in Operations; confirm the driver is real and sustainable before updating the forecast.

### 2026-02-17 - Finance / Revenue [CRITICAL]
- **Target:** 58,533.02  |  **Actual:** -288,117.03  |  **Variance:** -592.23%
- **Root cause:** Revenue underperformed in Finance: actual -288117.03 missed target 58533.02 severely (-592.23%).
- **Action:** Convene a Finance war-room within 24h; audit pipeline, pricing, and top-account churn drivers.

### 2026-03-05 - Finance / Revenue [CRITICAL]
- **Target:** 35,602.72  |  **Actual:** -140,578.71  |  **Variance:** -494.85%
- **Root cause:** Revenue underperformed in Finance: actual -140578.71 missed target 35602.72 severely (-494.85%).
- **Action:** Convene a Finance war-room within 24h; audit pipeline, pricing, and top-account churn drivers.

### 2026-03-23 - Operations / Units_Produced [CRITICAL]
- **Target:** 1,217.32  |  **Actual:** 4,432.06  |  **Variance:** +264.08%
- **Root cause:** Units_Produced in Operations exceeded target 1217.32 severely (264.08%) - favorable but verify driver.
- **Action:** Validate the Units_Produced upside in Operations; confirm the driver is real and sustainable before updating the forecast.

## High Anomalies (Action This Week)

### 2026-01-05 - Finance / Revenue [HIGH]
- **Target:** 51,718.09  |  **Actual:** 44,665.93  |  **Variance:** -13.64%
- **Root cause:** Revenue underperformed in Finance: actual 44665.93 missed target 51718.09 moderately (-13.64%).
- **Action:** Trigger Finance commercial review this week; reforecast and deploy tactical promotion or save-the-deal motions.

### 2026-01-12 - Finance / Revenue [HIGH]
- **Target:** 54,750.03  |  **Actual:** 61,656.10  |  **Variance:** +12.61%
- **Root cause:** Revenue in Finance exceeded target 54750.03 moderately (12.61%) - favorable but verify driver.
- **Action:** Validate the Revenue upside driver in Finance; confirm sustainability and book an upside forecast revision.

### 2026-01-13 - Finance / Revenue [HIGH]
- **Target:** 52,289.43  |  **Actual:** 45,552.28  |  **Variance:** -12.88%
- **Root cause:** Revenue underperformed in Finance: actual 45552.28 missed target 52289.43 moderately (-12.88%).
- **Action:** Trigger Finance commercial review this week; reforecast and deploy tactical promotion or save-the-deal motions.

### 2026-01-21 - Operations / Units_Produced [HIGH]
- **Target:** 1,287.27  |  **Actual:** 1,470.25  |  **Variance:** +14.21%
- **Root cause:** Units_Produced in Operations exceeded target 1287.27 moderately (14.21%) - favorable but verify driver.
- **Action:** Validate the Units_Produced upside in Operations; confirm the driver is real and sustainable before updating the forecast.

### 2026-02-02 - Operations / Units_Produced [HIGH]
- **Target:** 1,221.49  |  **Actual:** 1,059.70  |  **Variance:** -13.25%
- **Root cause:** Units_Produced underperformed in Operations: actual 1059.7 missed target 1221.49 moderately (-13.25%).
- **Action:** Brief Operations owner on Units_Produced variance; require a remediation plan within this week.

### 2026-02-03 - Operations / Units_Produced [HIGH]
- **Target:** 1,105.69  |  **Actual:** 1,218.01  |  **Variance:** +10.16%
- **Root cause:** Units_Produced in Operations exceeded target 1105.69 moderately (10.16%) - favorable but verify driver.
- **Action:** Validate the Units_Produced upside in Operations; confirm the driver is real and sustainable before updating the forecast.

### 2026-02-04 - Operations / Units_Produced [HIGH]
- **Target:** 1,198.14  |  **Actual:** 1,064.26  |  **Variance:** -11.17%
- **Root cause:** Units_Produced underperformed in Operations: actual 1064.26 missed target 1198.14 moderately (-11.17%).
- **Action:** Brief Operations owner on Units_Produced variance; require a remediation plan within this week.

### 2026-02-19 - Operations / Units_Produced [HIGH]
- **Target:** 1,242.80  |  **Actual:** 1,045.22  |  **Variance:** -15.90%
- **Root cause:** Units_Produced underperformed in Operations: actual 1045.22 missed target 1242.8 moderately (-15.9%).
- **Action:** Brief Operations owner on Units_Produced variance; require a remediation plan within this week.

### 2026-03-03 - Finance / Revenue [HIGH]
- **Target:** 40,750.99  |  **Actual:** 45,425.51  |  **Variance:** +11.47%
- **Root cause:** Revenue in Finance exceeded target 40750.99 moderately (11.47%) - favorable but verify driver.
- **Action:** Validate the Revenue upside driver in Finance; confirm sustainability and book an upside forecast revision.

### 2026-03-09 - Finance / Revenue [HIGH]
- **Target:** 54,110.30  |  **Actual:** 61,697.47  |  **Variance:** +14.02%
- **Root cause:** Revenue in Finance exceeded target 54110.3 moderately (14.02%) - favorable but verify driver.
- **Action:** Validate the Revenue upside driver in Finance; confirm sustainability and book an upside forecast revision.

### 2026-03-14 - Finance / Revenue [HIGH]
- **Target:** 50,065.01  |  **Actual:** 55,879.15  |  **Variance:** +11.61%
- **Root cause:** Revenue in Finance exceeded target 50065.01 moderately (11.61%) - favorable but verify driver.
- **Action:** Validate the Revenue upside driver in Finance; confirm sustainability and book an upside forecast revision.

### 2026-03-15 - Operations / Units_Produced [HIGH]
- **Target:** 1,173.53  |  **Actual:** 1,391.14  |  **Variance:** +18.54%
- **Root cause:** Units_Produced in Operations exceeded target 1173.53 moderately (18.54%) - favorable but verify driver.
- **Action:** Validate the Units_Produced upside in Operations; confirm the driver is real and sustainable before updating the forecast.

### 2026-03-20 - Finance / Revenue [HIGH]
- **Target:** 45,766.03  |  **Actual:** 39,706.64  |  **Variance:** -13.24%
- **Root cause:** Revenue underperformed in Finance: actual 39706.64 missed target 45766.03 moderately (-13.24%).
- **Action:** Trigger Finance commercial review this week; reforecast and deploy tactical promotion or save-the-deal motions.

### 2026-03-26 - Finance / Revenue [HIGH]
- **Target:** 44,872.50  |  **Actual:** 40,140.34  |  **Variance:** -10.55%
- **Root cause:** Revenue underperformed in Finance: actual 40140.34 missed target 44872.5 moderately (-10.55%).
- **Action:** Trigger Finance commercial review this week; reforecast and deploy tactical promotion or save-the-deal motions.

### 2026-03-27 - Finance / Revenue [HIGH]
- **Target:** 52,248.87  |  **Actual:** 59,364.57  |  **Variance:** +13.62%
- **Root cause:** Revenue in Finance exceeded target 52248.87 moderately (13.62%) - favorable but verify driver.
- **Action:** Validate the Revenue upside driver in Finance; confirm sustainability and book an upside forecast revision.

### 2026-03-28 - Operations / Units_Produced [HIGH]
- **Target:** 1,133.31  |  **Actual:** 997.96  |  **Variance:** -11.94%
- **Root cause:** Units_Produced underperformed in Operations: actual 997.96 missed target 1133.31 moderately (-11.94%).
- **Action:** Brief Operations owner on Units_Produced variance; require a remediation plan within this week.

### 2026-03-29 - Finance / Revenue [HIGH]
- **Target:** 53,283.99  |  **Actual:** 47,540.39  |  **Variance:** -10.78%
- **Root cause:** Revenue underperformed in Finance: actual 47540.39 missed target 53283.99 moderately (-10.78%).
- **Action:** Trigger Finance commercial review this week; reforecast and deploy tactical promotion or save-the-deal motions.

## Systemic Pattern

- **Finance Revenue (3 of 5 Critical):** Three revenue days are physically impossible: two are NEGATIVE figures (-230,294 and -288,117) and one is -495% from target. Strongest hypothesis: a sign-flip / refund-batch / data-load error in the Finance pipeline. Negative actuals should never be produced by a clean sales ledger.
- **Operations Units Produced (2 of 5 Critical):** Two days spiked +264% (4,432 vs 1,217) and +411% (6,630 vs 1,296) above target. Most likely a duplicate-count event (e.g. double-injection, batch re-run, or unit-of-measure confusion).
- **Cross-domain:** No same-day correlation between Finance and Operations spikes - the failures look independent, so the root cause is per-pipeline rather than a shared upstream event.

## Recommended Next Step

1. **Validate the 3 negative-revenue dates (2026-01-11, 2026-02-17, 2026-03-05)** against the source ledger - sign-flip bugs typically cluster in batch jobs.
2. **Audit the 2 Operations spikes (2026-01-26, 2026-03-23)** for duplicate-write events or unit-of-measure changes.
3. **Re-tune the High-risk threshold** if the 17 High rows reflect normal daily noise - they sit between 10-25% deviation, which is within the expected +/-2 sigma band for this dataset (target_mean 50k, noise_std 4k for Finance).

---
*Generated by the JCorp Early Warning Radar engine. Variance formula: `(actual - target) / |target| * 100`. Risk thresholds: Critical >= 25%, High 10% to <25%, Low <10%.*