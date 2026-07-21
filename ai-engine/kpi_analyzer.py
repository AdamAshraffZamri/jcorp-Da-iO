import pandas as pd
import numpy as np
from sklearn.neighbors import NearestNeighbors
from sklearn.preprocessing import StandardScaler


class KPIAnalyzer:
    """
    Ingests, cleans, and performs basic anomaly detection on KPI time-series data.
    """

    def __init__(self, filepath: str):
        self.filepath = filepath
        self.df: pd.DataFrame = pd.DataFrame()
        self._load()

    # ------------------------------------------------------------------
    # 1. Ingestion
    # ------------------------------------------------------------------
    def _load(self) -> None:
        """Read the CSV into a DataFrame."""
        self.df = pd.read_csv(self.filepath)
        print(f"[Load]  Loaded {len(self.df)} rows from '{self.filepath}'.")

    # ------------------------------------------------------------------
    # 2. Cleaning
    # ------------------------------------------------------------------
    def clean(self) -> "KPIAnalyzer":
        """
        - Drop rows with any null values.
        - Convert the 'Date' column to pandas datetime.
        Returns self for method chaining.
        """
        before = len(self.df)
        self.df.dropna(inplace=True)
        dropped = before - len(self.df)
        if dropped:
            print(f"[Clean] Dropped {dropped} row(s) containing nulls.")
        else:
            print("[Clean] No null values found — dataset is clean.")

        self.df["Date"] = pd.to_datetime(self.df["Date"])
        self.df.sort_values("Date", inplace=True)
        self.df.reset_index(drop=True, inplace=True)
        print("[Clean] 'Date' column converted to datetime and rows sorted chronologically.")
        return self

    # ------------------------------------------------------------------
    # 3. Anomaly Detection — Z-score
    # ------------------------------------------------------------------
    def detect_outliers(self, column: str = "Actual_Value", threshold: float = 3.0) -> pd.DataFrame:
        """
        Detect statistical outliers in `column` using the Z-score method.

        A row is flagged as an outlier when |z| >= threshold (default = 3.0).
        Because Finance and Operations metrics have very different scales,
        the Z-score is computed *per Domain* so each group is normalised
        against its own distribution.

        Parameters
        ----------
        column    : numeric column to analyse (default 'Actual_Value')
        threshold : absolute Z-score cutoff     (default 3.0)

        Returns
        -------
        DataFrame of flagged rows, sorted by absolute Z-score descending.
        """
        if self.df.empty:
            raise RuntimeError("DataFrame is empty. Run clean() first.")

        # Compute per-domain Z-scores
        self.df["Z_Score"] = (
            self.df.groupby("Domain")[column]
            .transform(lambda x: (x - x.mean()) / x.std(ddof=0))
        )

        outliers = self.df[self.df["Z_Score"].abs() >= threshold].copy()
        outliers["Abs_Z"] = outliers["Z_Score"].abs()
        outliers.sort_values("Abs_Z", ascending=False, inplace=True)
        outliers.drop(columns=["Abs_Z"], inplace=True)

        print(f"\n{'='*65}")
        print(f"  Outlier Detection  |  column='{column}'  |  |Z| >= {threshold}")
        print(f"{'='*65}")

        if outliers.empty:
            print("  No outliers detected at the given threshold.")
        else:
            print(f"  {len(outliers)} outlier(s) found:\n")
            display_cols = ["Date", "Domain", "Metric_Name",
                            "Target_Value", "Actual_Value", "Z_Score"]
            print(
                outliers[display_cols]
                .to_string(index=True, float_format=lambda x: f"{x:,.2f}")
            )

        print(f"{'='*65}\n")
        return outliers

    # ------------------------------------------------------------------
    # 4. Anomaly Detection — KNN (Finance domain)
    # ------------------------------------------------------------------
    def detect_anomalies_knn(
        self,
        domain: str = "Finance",
        features: "list[str] | None" = None,
        n_neighbors: int = 5,
        contamination: float = 0.1,
    ) -> pd.DataFrame:
        """
        Use K-Nearest Neighbors distance scoring to classify data points in
        `domain` as 'Normal' or 'Anomaly'.

        Algorithm
        ---------
        1. Filter the dataset to the requested domain.
        2. Scale features with StandardScaler (zero mean, unit variance).
        3. Fit a NearestNeighbors model and compute each point's mean distance
           to its k nearest neighbours — the *kNN anomaly score*.
        4. Points whose score exceeds the (1 - contamination) quantile are
           labelled 'Anomaly'; the rest are labelled 'Normal'.

        Parameters
        ----------
        domain        : domain to analyse             (default 'Finance')
        features      : columns used for fitting      (default ['Target_Value', 'Actual_Value'])
        n_neighbors   : k for kNN                     (default 5)
        contamination : expected fraction of outliers (default 0.10 → top 10 %)

        Returns
        -------
        Full domain DataFrame with an added 'KNN_Label' column.
        """
        if features is None:
            features = ["Target_Value", "Actual_Value"]

        if self.df.empty:
            raise RuntimeError("DataFrame is empty. Run clean() first.")

        domain_df = self.df[self.df["Domain"] == domain].copy()

        if domain_df.empty:
            raise ValueError(f"No rows found for domain='{domain}'.")

        if len(domain_df) <= n_neighbors:
            raise ValueError(
                f"Not enough rows ({len(domain_df)}) for n_neighbors={n_neighbors}."
            )

        # --- Scale features ---
        scaler = StandardScaler()
        X_scaled = scaler.fit_transform(domain_df[features])

        # --- Fit kNN (n_neighbors + 1 to exclude self) ---
        knn = NearestNeighbors(n_neighbors=n_neighbors + 1, algorithm="ball_tree")
        knn.fit(X_scaled)

        distances, _ = knn.kneighbors(X_scaled)
        # distances[:,0] is always 0 (self); take mean of the k true neighbours
        knn_scores = distances[:, 1:].mean(axis=1)

        domain_df["KNN_Score"] = knn_scores

        # --- Threshold: top `contamination` fraction = Anomaly ---
        threshold = np.quantile(knn_scores, 1.0 - contamination)
        domain_df["KNN_Label"] = np.where(
            domain_df["KNN_Score"] >= threshold, "Anomaly", "Normal"
        )

        anomalies = domain_df[domain_df["KNN_Label"] == "Anomaly"].sort_values(
            "KNN_Score", ascending=False
        )

        print(f"\n{'='*65}")
        print(f"  KNN Anomaly Detection  |  domain='{domain}'  |  k={n_neighbors}")
        print(f"  features={features}  |  contamination={contamination}")
        print(f"  Score threshold (≥): {threshold:.4f}")
        print(f"{'='*65}")

        if anomalies.empty:
            print("  No anomalies detected.")
        else:
            print(f"  {len(anomalies)} anomaly/anomalies flagged:\n")
            display_cols = ["Date", "Domain", "Metric_Name",
                            "Target_Value", "Actual_Value", "KNN_Score", "KNN_Label"]
            print(
                anomalies[display_cols]
                .to_string(index=True, float_format=lambda x: f"{x:,.4f}")
            )

        print(f"{'='*65}\n")
        return domain_df

    # ------------------------------------------------------------------
    # 5. Predictive Anomaly Detection — KNN 95th-percentile threshold
    # ------------------------------------------------------------------
    def predict_anomalies_knn(
        self,
        features: "list[str] | None" = None,
        n_neighbors: int = 5,
        percentile: float = 95.0,
    ) -> tuple[pd.DataFrame, str]:
        """
        Train a KNN model on the full dataset and flag rows whose mean
        distance to their 5 nearest neighbours exceeds the 95th percentile
        of all distances as 'Anomaly'.

        Algorithm
        ---------
        1. Scale ['Target_Value', 'Actual_Value'] with StandardScaler.
        2. Fit NearestNeighbors(k=5) on the scaled feature matrix.
        3. Compute each point's mean distance to its k true neighbours
           (self-distance excluded).
        4. Derive the anomaly threshold as np.percentile(scores, 95).
        5. Flag rows where score >= threshold as 'Anomaly'.
        6. Return the anomaly-only DataFrame and its JSON representation.

        Parameters
        ----------
        features    : feature columns to use (default ['Target_Value', 'Actual_Value'])
        n_neighbors : k for kNN              (default 5)
        percentile  : distance percentile cutoff (default 95.0)

        Returns
        -------
        (anomaly_df, json_string)
            anomaly_df  — DataFrame of flagged rows with KNN_Score & KNN_Label
            json_string — JSON export of anomaly_df (orient='records', date ISO format)
        """
        if features is None:
            features = ["Target_Value", "Actual_Value"]

        if self.df.empty:
            raise RuntimeError("DataFrame is empty. Run clean() first.")

        if len(self.df) <= n_neighbors:
            raise ValueError(
                f"Not enough rows ({len(self.df)}) for n_neighbors={n_neighbors}."
            )

        working_df = self.df.copy()

        # --- 1. Scale features ---
        scaler = StandardScaler()
        X_scaled = scaler.fit_transform(working_df[features])

        # --- 2. Fit kNN (request k+1 to exclude self at index 0) ---
        knn = NearestNeighbors(n_neighbors=n_neighbors + 1, algorithm="ball_tree")
        knn.fit(X_scaled)

        # --- 3. Compute anomaly scores ---
        distances, _ = knn.kneighbors(X_scaled)
        knn_scores = distances[:, 1:].mean(axis=1)   # exclude self-distance

        working_df["KNN_Score"] = knn_scores

        # --- 4. 95th-percentile threshold ---
        threshold = float(np.percentile(knn_scores, percentile))

        # --- 5. Label ---
        working_df["KNN_Label"] = np.where(
            working_df["KNN_Score"] >= threshold, "Anomaly", "Normal"
        )

        # --- 6. Isolate anomalies ---
        anomaly_df = (
            working_df[working_df["KNN_Label"] == "Anomaly"]
            .sort_values("KNN_Score", ascending=False)
            .reset_index(drop=True)
            .copy()
        )

        # Serialize Date to ISO string so JSON is human-readable
        export_df = anomaly_df.copy()
        export_df["Date"] = export_df["Date"].dt.strftime("%Y-%m-%d")
        json_string = export_df.to_json(orient="records", indent=2)

        # --- Print summary ---
        print(f"\n{'='*65}")
        print(f"  Predictive KNN Detection  |  k={n_neighbors}  |  p{percentile:.0f} threshold")
        print(f"  features={features}")
        print(f"  Distance threshold (≥ p{percentile:.0f}): {threshold:.4f}")
        print(f"{'='*65}")

        if anomaly_df.empty:
            print("  No anomalies detected at the given percentile threshold.")
        else:
            print(f"  {len(anomaly_df)} anomaly/anomalies flagged:\n")
            display_cols = ["Date", "Domain", "Metric_Name",
                            "Target_Value", "Actual_Value", "KNN_Score", "KNN_Label"]
            print(
                anomaly_df[display_cols]
                .to_string(index=True, float_format=lambda x: f"{x:,.4f}")
            )
            print(f"\n  JSON export:\n{json_string}")

        print(f"{'='*65}\n")
        return anomaly_df, json_string


# ------------------------------------------------------------------
# Entry point
# ------------------------------------------------------------------
if __name__ == "__main__":
    analyzer = KPIAnalyzer("sample_kpi_data.csv")
    analyzer.clean()

    # Method 3 — Z-score outlier detection (all domains)
    analyzer.detect_outliers(column="Actual_Value", threshold=3.0)

    # Method 4 — KNN anomaly detection (Finance domain, contamination-based)
    analyzer.detect_anomalies_knn(
        domain="Finance",
        features=["Target_Value", "Actual_Value"],
        n_neighbors=5,
        contamination=0.10,
    )

    # Method 5 — Predictive KNN detection (all domains, 95th-percentile threshold)
    anomaly_df, json_out = analyzer.predict_anomalies_knn(
        features=["Target_Value", "Actual_Value"],
        n_neighbors=5,
        percentile=95.0,
    )
