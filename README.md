# WB_CancelOrderEmail — Magento 2 Order Cancellation Email

[![Magento 2](https://img.shields.io/badge/Magento-2.4.x-orange.svg)](https://magento.com)
[![PHP](https://img.shields.io/badge/PHP-7.4%20|%208.1%20|%208.2-blue.svg)](https://php.net)
[![License: OSL-3.0](https://img.shields.io/badge/License-OSL--3.0-green.svg)](https://opensource.org/licenses/OSL-3.0)
[![Version](https://img.shields.io/badge/Version-1.0.0-brightgreen.svg)](https://github.com/wajahatbashir/magento2-cancel-order-email)

---

## Overview

By default, Magento 2 does **not** send any email notification to the customer when an order is cancelled from the Admin Panel. This module fills that gap.

**WB_CancelOrderEmail** listens to the `order_cancel_after` event and automatically sends a professionally formatted HTML cancellation email to the customer. Optionally, it can also notify one or more admin email addresses simultaneously.

---

## Features

- Sends a cancellation email to the customer automatically when an order is cancelled via Admin
- Configurable email sender identity (General Contact, Sales, Support, etc.)
- Supports custom email templates — editable from **Marketing > Email Templates**
- Optional admin notification with support for multiple comma-separated email addresses
- Enable/disable the module from Admin without touching code
- Logs all sent/failed emails to Magento's standard log
- Fully compatible with Magento's transactional email system

---

## Compatibility

| Component | Version |
|-----------|---------|
| Magento Open Source | 2.4.x |
| Magento Commerce (Adobe Commerce) | 2.4.x |
| PHP | 7.4, 8.1, 8.2 |

---

## Installation

### Method 1 — Composer (Recommended)

```bash
composer require wb/module-cancel-order-email
php bin/magento module:enable WB_CancelOrderEmail
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy -f
php bin/magento cache:flush
```

### Method 2 — Manual Installation

1. Create the directory `app/code/WB/CancelOrderEmail/`
2. Copy all module files into that directory
3. Run the following commands from your Magento root:

```bash
php bin/magento module:enable WB_CancelOrderEmail
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy -f
php bin/magento cache:flush
```

---

## Configuration

Navigate to:

**Admin Panel → Stores → Configuration → Sales → Cancel Order Email Settings**

### General Settings

| Field | Description | Default |
|-------|-------------|---------|
| Enable Module | Enable or disable the module | Yes |
| Email Template | Select the transactional email template to use | Order Cancellation Email (built-in) |
| Email Sender | The store identity used as the sender | Sales |

### Admin Notification

| Field | Description | Default |
|-------|-------------|---------|
| Notify Admin on Order Cancel | Also send the cancellation email to admin | No |
| Admin Email Address | One or more admin emails (comma-separated) | _(empty)_ |

> **Note:** The Admin Email Address field is only visible when Notify Admin is set to **Yes**.

---

## Email Template

The module ships with a default HTML email template located at:

```
view/frontend/email/order_cancel_template.html
```

The template includes:

- Customer name greeting
- Order number (bold, linked context)
- Order date
- Order total (plain text, no HTML artifacts)
- Cancellation status in red
- Refund notice
- Store name footer

### Customising the Template

You can override the template without touching module files:

1. Go to **Marketing → Communications → Email Templates**
2. Click **Add New Template**
3. Under **Load default template**, select **Order Cancellation Email**
4. Click **Load Template**, make your changes, and save
5. Go back to **Stores → Configuration → Sales → Cancel Order Email Settings**
6. Under **Email Template**, select your newly created custom template

---

## Module Structure

```
WB/CancelOrderEmail/
├── etc/
│   ├── adminhtml/
│   │   └── system.xml           # Admin configuration fields
│   ├── config.xml               # Default configuration values
│   ├── email_templates.xml      # Registers the email template
│   ├── events.xml               # Observes order_cancel_after event
│   └── module.xml               # Module declaration
├── Model/
│   └── Config.php               # Config reader helper
├── Observer/
│   └── SendCancelOrderEmail.php # Core logic — builds and sends the email
├── view/
│   └── frontend/
│       └── email/
│           └── order_cancel_template.html  # Default email template
├── composer.json
├── registration.php
└── README.md
```

---

## How It Works

1. Admin cancels an order from **Sales → Orders → Cancel**
2. Magento fires the `order_cancel_after` event
3. `SendCancelOrderEmail` observer picks up the event
4. Module checks if it is enabled for the store
5. Builds the email using Magento's `TransportBuilder` with the configured template, sender, and template variables
6. Sends the email to the customer's registered email address
7. If Admin Notification is enabled, sends the same email to each configured admin address
8. Logs success or failure to `var/log/system.log`

---

## Events Used

| Event | Description |
|-------|-------------|
| `order_cancel_after` | Fired by `Magento\Sales\Model\Order::cancel()` after the order status is set to Cancelled |

---

## Template Variables

The following variables are available in the email template:

| Variable | Description |
|----------|-------------|
| `{{var order.increment_id}}` | Order increment ID (e.g. `000030147-1`) |
| `{{var order.getCustomerName()}}` | Full customer name |
| `{{var order.getCreatedAtFormatted(2)}}` | Order creation date (localised) |
| `{{var formatted_grand_total}}` | Grand total as plain formatted currency string |
| `{{var store_name}}` | Store frontend name |
| `{{var store}}` | Store object |

---

## Uninstallation

```bash
php bin/magento module:disable WB_CancelOrderEmail
php bin/magento setup:upgrade
php bin/magento cache:flush
```

To completely remove:

```bash
composer remove wb/module-cancel-order-email
php bin/magento setup:upgrade
php bin/magento cache:flush
```

---

## Contributing

Contributions, bug reports, and feature requests are welcome.

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/my-feature`
3. Commit your changes: `git commit -m 'Add my feature'`
4. Push to the branch: `git push origin feature/my-feature`
5. Open a Pull Request

---

## Support

- **GitHub Issues:** [Report a bug or request a feature](https://github.com/wajahatbashir/magento2-cancel-order-email/issues)
- **Magento Stack Exchange:** [magento.stackexchange.com](https://magento.stackexchange.com)

---

## License

This module is licensed under the **Open Software License 3.0 (OSL-3.0)** and **Academic Free License 3.0 (AFL-3.0)**.

See [LICENSE](https://opensource.org/licenses/OSL-3.0) for full details.

---

## Changelog

### 1.0.0 — Initial Release
- Sends cancellation email to customer on order cancel from Admin
- Configurable sender identity and email template
- Optional admin notification with multi-email support
- Default HTML email template with order summary table
- Admin config panel under Sales section
