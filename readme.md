> 🌐 **Language:** [🇻🇳 Tiếng Việt](./readme_vi.md) · 🇬🇧 English (current)

# ShopDiscount — Coupon / Discount plugin for GP247 / S-Cart

## Introduction
ShopDiscount is a plugin that lets you create and manage **discount codes (coupons)** for an online store
running on GP247 / S-Cart 2.0. An administrator creates the discount codes; customers enter a code at the
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
| GP247 Core | **2.0 or later** |
| Required package | **gp247/shop** (for selling websites) |
| Plugin version | 2.0 |

> Without `gp247/shop` installed, the plugin will not work correctly (it serves the shopping features).

## Installation
There are 3 ways to install (like any GP247 extension). The fastest one, when you already have the plugin
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

**Q10: I'm on an old (1.x) version — how do I upgrade?**
This 2.0 release targets GP247 Core 2.0. GP247 2.0 does not support a direct upgrade from 1.x — install it
on a Core 2.0 site. The code data structure is kept the same as before.

---

<sub>📅 **Last updated:** 2026-07-31 · ✍️ **Author:** GP247</sub>
