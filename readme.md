> 🌐 **Language:** [🇻🇳 Tiếng Việt](./readme_vi.md) · 🇬🇧 English (current)

# ShopDiscount — Coupon / Discount plugin for GP247 / S-Cart

## Introduction
ShopDiscount is a plugin that lets you create and manage **discount codes (coupons)** for an online store
running on GP247 / S-Cart 3.0. An administrator creates the discount codes; customers enter a code at the
**checkout step** to get money off their order. This document is written for shop owners and admins (no
coding required): by the end you will know how to install it, create discount codes, and understand how
customers use them.

## Key features
- **Two discount types:**
  - **Point** — a **fixed amount** off (e.g. $5 off).
  - **Percent (%)** — a **percentage** off the order (e.g. 10% off).
- **Usage limit** per code (e.g. usable only 100 times).
- **Login required** (optional): only signed-in customers can use the code, and each customer can use it once.
- **Expiry date** for a code.
- **Enable / disable** any code at any time.
- **Multi-store support:** choose which store(s) a code applies to.
- **Apply at checkout instantly:** the customer enters a code → the discount line and order total update
  right away (no page reload).

## Requirements
| Component | Requirement |
| --- | --- |
| GP247 Core | **3.0** |
| Required package | **gp247/shop** (for selling websites) |
| Plugin version | 3.0 |

> Without `gp247/shop` installed, the plugin will not work correctly (it serves the shopping features).

## Installation
There are 4 ways to install (like any GP247 extension, including the command line). The fastest one, when you already have the plugin
folder:

1. Copy the plugin folder to the correct location on the server:

   ```
   app/GP247/Plugins/ShopDiscount
   ```

2. Log in to **admin** with an account that can manage extensions.
3. Open the **Plugin** menu, find **ShopDiscount** in the list, and click **Install**.
   On success you will see an "install success" message and a new menu item named **Discount**
   (Coupon/Discount) appears in admin.
4. (If admin still shows the old state) open a **Terminal** at the website root and run this line, then
   press Enter, to clear the cache:

   ```
   php artisan optimize:clear
   ```

> Prefer to install **Online (extension library)** or by **Importing a .zip file**? See the general
> installation guide:
> [Installing Plugins & Templates for GP247](https://github.com/gp247net/gp247-docs/blob/main/extension/install-extension.md).

### Install from the command line (CLI, gp247 3.x)

Since gp247 3.x you can download **ShopDiscount** from the GP247 library and install it straight from the command line, without opening the admin. Open a terminal in the website's root folder and run:

```bash
# 1) Once per website: register the (free) API License that connects the site to the GP247 library
php artisan gp247:ext-register-license

# 2) Download the plugin from the library and install it
php artisan gp247:ext-install --type=plugin --key=ShopDiscount
```

- Before step 1, make sure `APP_URL` in `.env` is the website's **real domain** (not `http://localhost`) — the license is bound to that domain.
- Once installed, the plugin is **enabled** and caches are refreshed automatically; nothing else is needed in the admin.
- The command checks the requirements declared in `gp247.json` (core version, composer packages, required plugins) and stops with a clear message if something is missing.
- This plugin requires the `gp247/shop` package; if it is missing, the command stops and tells you.
- If the folder `app/GP247/Plugins/ShopDiscount` is already on the server (copied manually or shipped with the installer), the command **installs it in place** instead of downloading it again.
- The command refuses a plugin that is already installed. To move to a newer version, run `php artisan gp247:ext-update --type=plugin --key=ShopDiscount`.
- Append `--json` to get machine-readable output (for scripts/CI).
- The command replaces steps 1–3 above. Step 4 still applies: if admin still shows the old state (no **Discount** menu yet), run `php artisan optimize:clear`.
- More: [Installing Plugins & Templates](https://github.com/gp247net/gp247-docs/blob/main/extension/install-extension.md) · [Command reference](https://github.com/gp247net/gp247-docs/blob/main/system/command-line-reference.md).

## Usage (1): Admin creates a discount code
1. In admin, open the **Discount** (Coupon/Discount) menu.
2. Click **Add new**.
3. Fill in the fields:
   - **Code** — the code the customer will type (e.g. `SALE10`). Use only letters, digits and `. - _`.
   - **Reward** — the discount value: for *Point* this is an **amount**; for *Percent* this is a **percentage**.
   - **Type** — choose **Point** (amount off) or **Percent (%)** (percentage off).
   - **Description** — a note for the code (optional).
   - **Limit** — the maximum number of times the code can be used.
   - **Login require** — tick this if customers must be signed in to use the code.
   - **Expires** — the expiry date (pick from the calendar).
   - **Status** — tick to **enable** the code (untick to disable).
   - **(Multi-store only)** choose the **store(s)** the code applies to.
4. Click **Submit** to save. The new code appears in the list on the right; the **Used** column shows how
   many times it has been used out of the allowed total (e.g. `3/100`).

## Usage (2): Customer applies a code at checkout
1. The customer adds products to the cart and proceeds to **Checkout**.
2. At the **order confirmation step**, the customer types the code into the **coupon** field and clicks **Apply**.
3. If the code is valid, a **discount** line appears and the **order total updates immediately**. To drop
   the code, click **Remove**.

A code is accepted only when it is: still valid (enabled and not expired), **has uses left**, applies to
the correct **store**, and (if the code requires it) the customer is **signed in** and has not used this
code before.

## Enable / disable / uninstall the plugin
- Go to admin → **Plugin** → **ShopDiscount**.
- **Enable / Disable** to turn it on or off temporarily (no discount data is lost).
- **Uninstall** to remove it completely — this **deletes the plugin's discount data**, so consider carefully first.

## Q&A
**Q1: Where does the customer enter the discount code?**
At the **confirmation step** of the checkout page there is a coupon input. Enter the code and click Apply.

**Q2: What is the difference between "Point" and "Percent"?**
*Point* takes a **fixed amount** off (e.g. $5). *Percent* takes a **percentage** off the order (e.g. 10%).
The **Reward** field is the number for whichever type you chose.

**Q3: I installed it but the "Discount" menu doesn't show in admin?**
Run `php artisan optimize:clear` to clear the cache, then reload admin. Also check the plugin is
**installed and enabled** in the Plugin menu.

**Q4: The customer applied a code but sees no discount?**
Check the code is still **enabled (Status)**, **not expired**, **has uses left** (Used below Limit), and
matches the **store**. If the code has "Login require" on, the customer must be **signed in**.

**Q5: How many times can one code be used?**
As many as the **Limit** you set. Once Used reaches Limit, the code reports "out of uses" and stops working.

**Q6: Does uninstalling the plugin delete the codes I created?**
Yes. Uninstall removes the discount data. If you only want to pause it, use **Disable** instead of Uninstall.

**Q7: How do codes work when I run multiple stores?**
When multi-store is enabled, you choose the **store(s)** for each code as you create it. The code is only
valid in the chosen store(s).

**Q8: Does the plugin need gp247/shop?**
Yes. ShopDiscount serves shopping features, so `gp247/shop` **must** be installed.

**Q9: What does "Login require" mean?**
It means only **signed-in** customers can use that code, and each account can use that code **once**.

**Q10: How do I upgrade to the 3.0 version?**
This 3.0 release targets **GP247 Core 3.0**. If you are on a 2.x plugin, you can update in place: open
admin → **Plugin → From library**, click **Check update**, then click **Update 3.0** (the plugin allows a
direct update from 2.0 onward — see `requireUpdateFrom`). The code data structure is kept the same, so your
existing discount codes are preserved. If you are still on an old 1.x version, install 3.0 fresh on a Core
3.0 site (a direct 1.x → 3.0 upgrade is not supported).

## Change history
<!-- Only when logic/behavior changed. Newest row on top. One row per day: merge same-day changes into the existing row, never add a new row for the same date. -->

| Date | GP247 version | Change |
| --- | --- | --- |
| 2026-08-30 | GP247 Core 3.0 | Release 3.0: targets GP247 Core 3.0 (`requireCore` 3.0); in-place update supported from plugin 2.0 onward. Feature set and data structure unchanged. |

---

<sub>📅 **Last updated:** 2026-09-25 · ✍️ **Author:** GP247</sub>
