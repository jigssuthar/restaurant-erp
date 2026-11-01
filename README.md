<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Admin dashboard setup

Run the database migrations, then create an administrator account. The command prompts for the name and a hidden password:

```bash
php artisan migrate
php artisan admin:create-admin admin@example.com
```

Self-service registration creates a restaurant workspace, its first branch and settings, a tenant-scoped owner account, and a configurable 30-day trial. A platform administrator is a separate account with no organization; create one securely with `php artisan admin:create-admin admin@example.com`. `php artisan admin:make-admin admin@example.com` promotes an existing account to platform administrator and removes its tenant assignment.

Platform administrators manage draft and published subscription plans under **Subscription Plans**. Prices are intentionally unset until configured. To accept payments, create recurring plans in Razorpay, add each Razorpay plan ID to its matching DineCentral plan, publish it, and configure `RAZORPAY_KEY_ID`, `RAZORPAY_KEY_SECRET`, and `RAZORPAY_WEBHOOK_SECRET` in the deployment environment. Configure the Razorpay webhook URL as `/billing/razorpay/webhook` and subscribe to `subscription.authenticated`, `subscription.activated`, `subscription.charged`, `subscription.halted`, `subscription.cancelled`, `subscription.completed`, `payment.captured`, and `payment.failed`. Never commit live gateway credentials. Customers can review billing history and cancel renewal from Billing & Plans.

Restaurant owners and managers are organization-scoped; staff are branch-scoped. Platform administration routes are restricted to platform administrators. Trial/subscription status gates the authenticated workspace and table QR ordering, while billing remains accessible after expiry. Configured branch, staff, product and table quotas are enforced when creating records; an empty limit means unlimited.

### SaaS production checklist

- Set `APP_NAME=DineCentral`, `APP_ENV=production`, `APP_DEBUG=false`, a strong `APP_KEY`, canonical HTTPS `APP_URL`, and a production database before deployment.
- Configure mail delivery and test password-reset emails; use a trusted mail sender and review the privacy policy, terms, support contact, tax and refund disclosures with the business/legal owner before accepting customers.
- Keep Razorpay credentials in the hosting provider's secret manager. Configure the webhook secret and public HTTPS webhook URL in Razorpay, then test checkout, renewal, cancellation, failed payments, and duplicate/out-of-order webhook delivery in test mode.
- Run `php artisan migrate --force`, build frontend assets with `npm run build`, and verify writable storage/cache paths, scheduled tasks/queue workers if enabled, HTTPS, database backups, monitoring and restore procedures.
- Create the initial platform administrator with `php artisan admin:create-admin ...`; never use tenant accounts as platform administrators. Configure and test at least one Razorpay plan before publishing it.

Orders support dine-in, takeaway, and delivery workflows. Kitchen Display users progress tickets from pending to preparing and ready; service staff mark them served independently of payment. Table occupancy remains active until dine-in orders are served and settled. Cash, Card, UPI, and other payments are recorded separately from order fulfillment. Organization Settings configure currency, tax, service charge, delivery fee, and receipt text. Tax and service charge are calculated from the item subtotal; delivery fee applies only to delivery orders.

Each dining table has its own QR code on its table detail page. The responsive customer menu supports category filtering, search, product images, a live cart and order totals, complete order confirmation, and table service requests (call waiter, request bill, or request water). Print or download the code for customers to submit unpaid dine-in orders; payment is collected at the restaurant. Waiters and captains use the standalone `/service-app` URL to manage table requests, serving, and outstanding payments; chefs and kitchen managers use the standalone `/kitchen-display` URL. These apps have their own screens and do not appear in the admin dashboard sidebar. Replacing a table's QR code invalidates previously printed links. Configure `APP_URL` to the public site URL before printing QR codes for production.

## Demo data

After migrations, populate the organization, branches, staff, categories, products, tables, orders, settings, and sample contact enquiries with repeatable demo records:

```bash
php artisan migrate --seed
```

For local sign-in as the seeded Managers or Staff, set a private `DEMO_USER_PASSWORD` in `.env` before seeding. Without it, seeded demo users receive randomly generated passwords. Create your own Admin account securely with `php artisan admin:create-admin admin@example.com`.

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
