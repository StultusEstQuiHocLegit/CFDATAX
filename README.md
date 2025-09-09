# CFDATA

This project gathers bankruptcy related 8‑K filings from the SEC EDGAR system and enriches them with company metadata and historical financial data.

Three CSV files are produced (and reports as attachments can be saved too if the correspoding part is commented in / activated) for bankrupt companies when running `DataCollector0.php`: `main.csv`, `financials.csv` and `reports.csv`.
In addition, we also select solvent/healthy/non-bankrupt companies and save their information in: `main_solvent.csv`, `financials_solvent.csv` and `reports_solvent.csv`, whereas in `main_solvent.csv` of course not all columns are filled out (only the columns regarding general company information).

## main.csv

One row per company containing the first bankruptcy filing found in the scanned year.

| Column | Description |
| --- | --- |
| BankruptcyDate | date extracted from the filing text |
| FilingDate | date the 8‑k was filed |
| FilingType | form type of the filing |
| BankruptcyChapter | bankruptcy chapter if detected |
| CourtName | court handling the bankruptcy |
| CaseNumber | bankruptcy case number |
| AccessionNumber | sec accession number |
| FilingURL | url of the filing |
| CIK | central index key |
| CompanyName | company name |
| exchange | primary exchange of the company |
| IndustrySICCode | industry sic code |
| FiscalYearEnd | company's fiscal year end |
| BusinessAddressState | business address state |
| BusinessAddressZIPCode | business address zip code |
| BusinessAddressCity | business address city |
| BusinessAddressStreet | business address street |
| BusinessAddressHouseNumber | business address house number (if available) |
| StateOfIncorporation | state of incorporation |
| Website | company website |
| InvestorWebsite | investor relations website |
| SIC | sic code |
| SICDescription | description of the sic code |

Example for general company data:
- [Apple](https://data.sec.gov/submissions/CIK0000320193.json)

## financials.csv

Historical financial metrics for each company from `YEAR - 10` through `YEAR` (2014‑2024 when YEAR=2024). Each row represents one year.

| Column | Description |
| --- | --- |
| idpk | sequential row identifier |
| CIK | central index key matching main.csv |
| year | fiscal year of the data |
| assets | total assets |
| CurrentAssets | current assets |
| NoncurrentAssets | non‑current assets |
| liabilities | total liabilities |
| CurrentLiabilities | current liabilities |
| NoncurrentLiabilities | non‑current liabilities |
| LiabilitiesAndStockholdersEquity | liabilities and stockholders' equity |
| equity | stockholders' equity |
| CommonStockValue | value of common stock |
| RetainedEarningsAccumulatedDeficit | retained earnings or accumulated deficit |
| AccumulatedOtherComprehensiveIncomeLoss | aoci |
| MinorityInterest | minority interest |
| revenues | total revenues |
| SalesRevenueNet | net sales revenue |
| CostOfGoodsSold | cost of goods sold |
| GrossProfit | gross profit |
| OperatingExpenses | operating expenses |
| SellingGeneralAndAdministrativeExpense | sg&a expense |
| ResearchAndDevelopmentExpense | research and development expense |
| OperatingIncomeLoss | operating income or loss |
| InterestExpense | interest expense |
| IncomeBeforeIncomeTaxes | income before taxes |
| IncomeTaxExpenseBenefit | income tax expense or benefit |
| NetIncomeLoss | net income or loss |
| PreferredStockDividendsAndOtherAdjustments | preferred stock dividends and other adjustments |
| NetIncomeLossAvailableToCommonStockholdersBasic | net income available to common stockholders (basic) |
| EarningsPerShareBasic | basic earnings per share |
| EarningsPerShareDiluted | diluted earnings per share |
| WeightedAverageNumberOfSharesOutstandingBasic | weighted average shares outstanding (basic) |
| WeightedAverageNumberOfDilutedSharesOutstanding | weighted average shares outstanding (diluted) |
| NetCashProvidedByUsedInOperatingActivities | net cash provided by or used in operating activities |
| NetCashProvidedByUsedInInvestingActivities | net cash provided by or used in investing activities |
| NetCashProvidedByUsedInFinancingActivities | net cash provided by or used in financing activities |
| CashAndCashEquivalentsPeriodIncreaseDecrease | increase/decrease in cash and cash equivalents |
| CashAndCashEquivalentsAtCarryingValue | cash and cash equivalents at carrying value |
| PaymentsToAcquirePropertyPlantAndEquipment | capital expenditures |
| ProceedsFromIssuanceOfCommonStock | proceeds from issuing common stock |
| PaymentsOfDividends | dividend payments |
| RepaymentsOfDebt | debt repayments |
| ProceedsFromIssuanceOfDebt | proceeds from issuing debt |
| DepreciationAndAmortization | depreciation and amortization |
| InventoryNet | net inventory |
| AccountsReceivableNetCurrent | net current accounts receivable |
| AccountsPayableCurrent | current accounts payable |
| Goodwill | goodwill |
| IntangibleAssetsNetExcludingGoodwill | intangible assets excluding goodwill |
| PropertyPlantAndEquipmentNet | property, plant and equipment (net) |
| LongTermDebtNoncurrent | non‑current long‑term debt |
| ShortTermBorrowings | short‑term borrowings |
| IncomeTaxesPayableCurrent | current income taxes payable |
| EntityRegistrantName | entity registrant name |
| EntityCentralIndexKey | central index key (repeated) |
| TradingSymbol | trading symbol |
| EntityIncorporationStateCountryCode | state or country of incorporation |
| EntityFilerCategory | filer category |
| DocumentPeriodEndDate | document period end date |
| DocumentFiscalPeriodFocus | document fiscal period focus |
| DocumentFiscalYearFocus | document fiscal year focus |
| DocumentType | document type |
| AmendmentFlag | amendment flag |
| CurrentFiscalYearEndDate | current fiscal year end date |

Example for company details data:
- [Apple](https://data.sec.gov/api/xbrl/companyfacts/CIK0000320193.json)

## reports.csv

Historical reports for each company from `YEAR - 10` through `YEAR` (2014‑2024 when YEAR=2024). Each row represents one year.

| Column | Description |
| --- | --- |
| idpk | sequential row identifier |
| CIK | central index key matching main.csv |
| year | fiscal year of the data |
| AnnualReportLink | link to the HTML file with the annual report |
| QuarterlyReportLinkQ1 | link to the HTML file with the quarterly report for Q1 |
| QuarterlyReportLinkQ2 | link to the HTML file with the quarterly report for Q2 |
| QuarterlyReportLinkQ3 | link to the HTML file with the quarterly report for Q3 |

## ./AnnualReports/

Historical annual reports for each company from `YEAR - 10` through `YEAR` (2014‑2024 when YEAR=2024).
Saved in the format: `[CIK]_[YEAR]`.html

Example for annual report company data:
- [Apple](https://www.sec.gov/Archives/edgar/data/320193/000032019324000123/aapl-20240928.htm)

## ./QuarterlyReports/

Historical quarterly reports for each company from `YEAR - 10` through `YEAR` (2014‑2024 when YEAR=2024).
Saved in the format: `[CIK]_[YEAR]_["Q1" or "Q2" or "Q3" or "Q4"]`.html

Example for quarterly report company data:
- [Apple](https://www.sec.gov/Archives/edgar/data/320193/000032019325000057/aapl-20250329.htm)

### USA Federal Holidays for Year 2024

| Date        | Day of Week | Holiday Name                               |
| ----------- | ----------- | ------------------------------------------ |
| January 1   | Monday      | New Year’s Day                             |
| January 15  | Monday      | Birthday of Martin Luther King, Jr.        |
| February 19 | Monday      | Washington’s Birthday (aka Presidents Day) |
| May 27      | Monday      | Memorial Day                               |
| June 19     | Wednesday   | Juneteenth National Independence Day       |
| July 4      | Thursday    | Independence Day                           |
| September 2 | Monday      | Labor Day                                  |
| October 14  | Monday      | Columbus Day                               |
| November 11 | Monday      | Veterans Day                               |
| November 28 | Thursday    | Thanksgiving Day                           |
| December 25 | Wednesday   | Christmas Day                              |

