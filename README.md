# NextDNA Retail POS — Clothing Shop Billing, Inventory & Retail Management

A complete, production-ready Clothing Retail Billing, Inventory, and Store Operations application built for **NextDNA Technologies** using **Laravel 13**, **PHP 8.3**, **MySQL/MariaDB**, **Livewire 4**, **Alpine.js**, **Tailwind CSS**, and **Vite**.

Designed specifically for Indian apparel and garment retail operations with high-speed barcode keyboard-wedge scanning, size-and-colour matrix inventory, Goods Received Notes (GRN), customer loyalty & store credit, exchange difference settlement, multi-shift cash registers, GST HSN compliance, and robust thermal receipt printing (58mm/80mm ESC/POS, Browser, QZ Tray bridge, and LAN sockets).

---

## Key Modules & Capabilities

### 1. Fast Touch & Keyboard POS Billing
- **Keyboard-Wedge Barcode Scanning**: Immediate variant lookup on barcode/SKU scan with Web Audio scan confirmation sound and failure alarm.
- **Instant Product Search (`F2`)**: Full-text search across article code, product name, brand, color, size, and SKU.
- **Cart Management**: Fast quantity adjustment (`+/-`), line-item discounts (percent or amount), bill-level discounts, and line removal.
- **Hold & Resume Carts (`F8`)**: Hold multiple active carts with custom customer tags and restore them seamlessly.
- **Customer Account & Quick Add (`F7`)**: Phone number autocomplete, customer loyalty point accrual, and store credit balance lookup.
- **Split & Mixed Payment Checkout (`F9`)**: Pay bills combining Cash, UPI (with reference IDs), Credit/Debit Card, Gift Vouchers, and Store Credit.
- **Idempotency Protection**: Client and server idempotency keys prevent duplicate billing or stock overdraw on double-clicks or network retries.
- **Manager Discount Approval**: Strict server enforcement requiring manager override for discounts exceeding cashier limits.

### 2. Barcode Label & Hangtag Studio (`/barcode-labels`)
- **Unique Barcode Generation**: Generates mathematically valid GS1 EAN-13 barcodes with official Modulo-10 check-digit calculation (`890` Indian country prefix) or standard retail Code-128 alphanumeric sequences.
- **Scan-to-Queue**: Keyboard-wedge scanner input at the top allows cashiers and stock staff to scan any garment hangtag to instantly fetch details and add it to the print queue with copies automatically matched to current stock!
- **Garment Label Presets**:
  - **Hangtag (50mm × 38mm)**: Brand name, Article code, Size badge, Color, Barcode SVG, MRP with strike, Offer Price, Packed date, Store footer.
  - **Standard Garment Sticker (50mm × 25mm)**: 2-inch adhesive polybag sticker.
  - **Compact Tag (38mm × 25mm)**: Socks, innerwear, and garment accessories.
  - **A4 Sticker Sheet (24-Up Grid)**: Laser/inkjet office sticker sheet printing.
- **Print Modes**: Browser Print (`window.print()` with calibrated `@media print` sheet margins) or Direct Thermal Label Printer dispatch via raw driver.
- **Batch Barcode Assignment**: 1-click generation of unique barcodes for all queued products or unbarcoded inventory.

### 3. Scan & Fetch Garment Details / Price Checker (`/product-lookup` or `F3`)
- **Instant Barcode Lookup**: Focus-locked scanner input captures barcodes/SKUs and immediately displays the complete product profile.
- **Multi-Location Live Stock**: Real-time breakdown of on-hand inventory across Retail Sales Floor, Backroom Warehouse, and Damaged/Defect room.
- **Sibling Variants Matrix**: One-click pills to switch between all available sizes (S, M, L, XL, XXL) and colors of the same article code.
- **Quick Actions**: 1-click **"Print Barcode Labels"**, **"Add to POS Billing Cart"**, or **"Quick Stock Adjustment"** directly from the scanner view.

### 4. Clothing Catalogue & Size-Color Matrix (`/catalogue`)
- **Apparel Hierarchy**: Categories, subcategories, brands, suppliers, seasons, collections, and size groups (XS–XXL, 28–44, Kids, Custom).
- **Matrix Generation**: Create full size-and-colour variant grids with automated SKU and GS1 EAN-13 barcode generation.
- **Pricing & Thresholds**: Purchase cost, selling price, MRP, wholesale price, and low-stock alerts per variant.
- **CSV Import / Export**: Bulk variant and catalogue import with preview, duplicate SKU detection, and error logging.

### 5. Inventory & Purchasing Operations
- **Real-Time Stock Ledger**: Auditable, immutable `StockMovement` ledger tracking every quantity change with `balance_after` and user attribution.
- **Negative Stock Protection**: Pessimistic database locking (`lockForUpdate`) strictly prevents concurrent inventory overdraw.
- **Purchase Orders & Goods Received Notes (GRN)**: Track supplier invoices, partial goods receipts, and automatically increment variant stock.
- **Stock Adjustments & Transfers**: Multi-location tracking (Retail floor, Warehouse, Damaged/Defective room) and inter-location transfers.

### 6. Returns & Size/Colour Exchanges
- **Invoice & Barcode Lookup**: Locate historical invoices to inspect eligible items.
- **Return Quantity Limits**: Hard constraint preventing returns exceeding `sold_quantity - previously_returned`.
- **Condition-Based Routing**: Resellable garments return to active retail stock; defective garments are routed to the damaged location.
- **Exchange Engine**: Seamlessly exchange sizes or garments. The system automatically computes price differences, creates exchange orders, and settles balances via cash, card, or store credit notes.

### 7. Multi-Driver Thermal Receipt Printer Management
- **Add Printer Wizard**: Interactive 3-step configuration for manufacturer, model, width, and connection type.
- **Supported Connection Types**:
  - **Browser Printing**: Pixel-perfect HTML receipt templates styled for 80mm, 58mm, and A4 invoices.
  - **Direct LAN ESC/POS Socket**: Raw ESC/POS byte streaming over TCP port 9100.
  - **QZ Tray Bridge**: Local USB/OS driver bridge with client-side WebSocket communication.
  - **Mock / Simulator Adapter**: Automated testing driver verifying byte streams and printer status.
- **Safe Hardware Disconnection**: Network or printer offline errors create failed `PrintJob` records while preserving the database sale intact.
- **Reprint Protection**: Explicit duplicate receipts marked `*** DUPLICATE / REPRINT ***`.

### 8. Cash Registers & Shifts
- **Shift Management**: Opening float entry, real-time cash payment tracking, petty store expenses, and cash in/out adjustments.
- **Shift Reconciliation**: Expected vs counted cash calculation with variance audit on register closure.

### 9. Indian GST & Financial Reports
- **Indian Retail Tax (GST)**: Exact integer-paise calculations for intra-state (CGST 2.5% + SGST 2.5%) and inter-state (IGST 5.0%).
- **Round-Off Handling**: Automatic rounding to nearest Rupee (`round_off_amount`).
- **Reports Suite**:
  - Daily Sales & Invoices Summary
  - Payment Reconciliation (Cash, UPI, Cards, Store Credit)
  - Gross Profit & Cost of Goods Sold (COGS)
  - GST HSN Tax Summary with exportable CSVs
  - Inventory Stock Valuation (Cost vs Retail basis)

### 10. Dynamic General Settings (`/settings/general`)
- **Store & Company Identity**: Dynamically configure Shop Name, Tagline, Phone, Support Email, Physical Address, City, State, Pin Code, and State GST Code (e.g. `33` for Tamil Nadu).
- **Taxation & GST Standards**: Manage Store GSTIN, Default Garment Tax Rate (5%), Tax-Inclusive/Exclusive pricing mode, Invoice Prefix (`INV`), and Return Prefix (`RET`).
- **POS Billing & Cashier Controls**: Set Cashier Max Permitted Discount %, enforce manager override rules, toggle negative stock checkout, enable walk-in customer billing, and configure round-off to nearest rupee.
- **Thermal Receipts Formatting**: Configure Receipt Header Store Title, Footer Return Policy message, paper width default (80mm vs 58mm), toggle CGST/SGST tax breakdown, and toggle "Powered by NextDNA Technologies" footer.
- **Barcode & Hangtag Defaults**: Choose default barcode standard (GS1 EAN-13 vs Code-128), set GS1 country prefix (`890`), manufacturer code (`2026`), and default label preset (`50x38mm`).
- **Localization**: Currency symbol (`₹`), code (`INR`), date format (`d/m/Y`), timezone (`Asia/Kolkata`), and language selection (`en` / `ta`).

---

## Technology Stack

- **Backend**: Laravel 13.x, PHP 8.3 (Strict types, Constructor Property Promotion)
- **Database**: MySQL / MariaDB 10.4+
- **Reactivity & Frontend**: Laravel Livewire 4.x, Alpine.js, Tailwind CSS
- **Code Quality**: PHPUnit (32 automated feature/unit tests), Laravel Pint
- **Precision Currency**: Integer minor-unit (paise) decimal arithmetic

---

## Installation & Setup

### 1. Requirements
- PHP 8.3 or higher with extensions: `pdo_mysql`, `mbstring`, `openssl`, `curl`, `json`
- MySQL 8.0+ or MariaDB 10.4+
- Composer 2.x
- Node.js & npm (optional for Vite asset building)

### 2. Clone and Install Dependencies
```bash
cd /opt/lampp/htdocs/cloth_billing
composer install
```

### 3. Environment Configuration
Copy `.env.example` to `.env` and configure your database:
```env
APP_NAME="NextDNA Retail POS"
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_TIMEZONE="Asia/Kolkata"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cloth_billing
DB_USERNAME=root
DB_PASSWORD=
# For local XAMPP/LAMPP Unix socket if TCP is restricted:
# DB_SOCKET=/opt/lampp/var/mysql/mysql.sock
```

### 4. Database Migration & Seed
Run migrations and load the retail clothing seed data (categories, sizes, products, stock, demo users, default printers):
```bash
php artisan migrate:fresh --seed
```

### 5. Launch Development Server
```bash
php artisan serve --port=8000
```
Visit `http://localhost:8000` in your web browser.

---

## Default User Accounts

All seed users have the default password: **`Nextdna@123`**

| Role | Email | Permissions |
|---|---|---|
| **Owner / Admin** | `admin@nextdna.com` | Full administrative access, settings, printers, reports, cost prices |
| **Branch Manager** | `manager@nextdna.com` | Shift management, discount overrides (>10%), returns approval, stock adjustments |
| **Cashier** | `cashier1@nextdna.com` | POS billing, customer registration, hold/resume carts, shift cash out |
| **Inventory Staff**| `inventory@nextdna.com` | Goods receipts (GRN), purchase orders, size-color matrix, transfers |
| **Accountant** | `accountant@nextdna.com` | Financial reports, GST tax summaries, supplier payments, cash reconciliation |

The login screen includes a **One-Click Demo Role Switcher** for instant testing.

---

## Keyboard Shortcuts (POS Screen)

Press **`F1`** at any time on the POS screen to display the shortcut cheat sheet:

| Key | Action |
|---|---|
| **`F1`** | Open Keyboard Shortcuts Modal |
| **`F2`** | Quick Product Catalogue & Variant Search |
| **`F7`** | Customer Lookup & Quick Registration |
| **`F8`** | Hold Current Cart / Resume Held Carts |
| **`F9`** | Open Checkout & Split Payment Modal |
| **`Escape`** | Close Open Modal / Return to Barcode Input |

---

## Thermal Printer Setup Guide

Navigate to **Settings → Printers** in the navigation bar.

### 1. Browser Printing (Default)
- No additional software required.
- Uses browser print dialog formatted at standard 80mm (42 columns) or 58mm (32 columns) receipt widths.

### 2. LAN ESC/POS Socket
- Connect your thermal printer (e.g. Epson TM-T82, TVS RP-3200, Posiflex) to the store local network via Ethernet or Wi-Fi.
- Assign a static IP address to the printer (e.g. `192.168.1.200`).
- In the **Add Printer Wizard**, choose **LAN ESC/POS Socket**, enter the IP address and port `9100`.
- Click **Run Diagnostic Test Print** to verify paper cut and text alignment.

### 3. QZ Tray Local Bridge (USB / Serial)
For USB receipt printers connected directly to client workstations:
1. Download and install [QZ Tray](https://qz.io/download/) on the cashier computer.
2. In **Printer Settings**, select **QZ Tray WebSocket Bridge**.
3. The browser connects to `ws://localhost:8182` and silently routes raw ESC/POS byte commands to the USB printer.

---

## Automated Test Suite

The application includes 44 automated tests with 183 assertions verifying all core business logic:

```bash
# Run all tests
vendor/bin/phpunit

# Run specific feature test suites
php artisan test --filter=GeneralSettingsTest
php artisan test --filter=BarcodeAndProductLookupTest
php artisan test --filter=PosBillingTest
php artisan test --filter=InventoryAndConcurrencyTest
php artisan test --filter=ReturnsAndExchangesTest
php artisan test --filter=PrinterManagementTest
php artisan test --filter=ReportsReconciliationTest
php artisan test --filter=TaxAndPricingTest
```

### Test Coverage Highlights
- **GeneralSettingsTest**: Dynamic persistence of store profile, GSTIN, invoice prefixes, cashier discount thresholds, negative stock flags, automatic branch synchronization, audit log creation, and dynamic thermal receipt header/footer rendering.
- **BarcodeAndProductLookupTest**: Valid GS1 EAN-13 generation with Modulo-10 checksum, Code-128 sequence uniqueness, vector SVG rendering, scan-to-fetch product lookup, scan-to-queue label printing, batch variant addition, bulk barcode generation.
- **PosBillingTest**: Stock decrement per variant, idempotency duplicate prevention, mixed payments, cashier discount thresholds, manager overrides.
- **InventoryAndConcurrencyTest**: Negative stock blocking, pessimistic locking (`lockForUpdate`), auditable stock ledger movements.
- **ReturnsAndExchangesTest**: Quantity constraint checks, resellable vs damaged stock routing, customer store credit refunds, size/color exchange difference settlement.
- **PrinterManagementTest**: ESC/POS 58mm/80mm column width formatting, failure tolerance (sales preserved when printers are offline), reprint duplicate labeling.
- **ReportsReconciliationTest**: Daily sales reconciliations, payment method grouping, COGS & gross margin, GST HSN tax report, streamed CSV export.
- **TaxAndPricingTest**: Indian GST split (CGST+SGST vs IGST), tax-inclusive reverse calculations, penny round-off, proportional bill discount allocation.

---

## Code Formatting

Follows the Laravel Pint style standard:
```bash
vendor/bin/pint --format agent
```

---

## License

Developed for NextDNA Technologies. All rights reserved.
