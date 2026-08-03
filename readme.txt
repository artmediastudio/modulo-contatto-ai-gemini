=== Modulo Contatto AI Gemini ===
Contributors: aiagentiintelligenti
Tags: contact form, gemini, ai, lead generation, chatbot
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Contact form that generates an AI feasibility report with Google Gemini for every submission, staying safely within the free tier.

== Description ==

Every time a visitor submits the form, the plugin:

1. Immediately sends a notification email to your address (this always happens, even if the AI part is temporarily paused).
2. Asks Google Gemini to analyze the request and generate a short report: summary, service category, estimated complexity, timeline, next steps.
3. Shows the report directly to the visitor right after submission.

= Always staying on the free tier =

Google offers a free tier for the "Flash" Gemini models, but the exact number of daily requests allowed varies per account and changes over time — so the plugin never assumes it. Two independent limits are available in the settings:

* **Total daily limit**: a maximum number of AI requests per day, shared across all visitors. It resets every night. Set it conservatively below your actual quota (check it on Google AI Studio → Rate Limits) and you will never be charged, whatever happens.
* **Per-IP limit**: blocks a single visitor (or bot) sending too many requests in a few seconds.

If the daily limit is reached, the form keeps working normally as a regular contact form (the email still arrives) but shows a configurable message instead of the AI report, until the next day.

It also includes an invisible honeypot field to discourage spam bots, with no external services.

= Usage =

1. Activate the plugin.
2. Go to Settings → Modulo Contatto AI and enter your free Google Gemini API key (available on Google AI Studio).
3. Customize service categories, notification email and the two limits.
4. Add the `[modulo_contatto_ai]` shortcode to any page or post.

Developed by [AI Agenti Intelligenti](https://www.agentiintelligenti.it), AI strategy consulting and autonomous agent development.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`, or install it from the WordPress plugin directory.
2. Activate the plugin from the WordPress Plugins menu.
3. Configure the API key under Settings → Modulo Contatto AI.
4. Add the `[modulo_contatto_ai]` shortcode wherever you want the form to appear.

== Frequently Asked Questions ==

= How do I get a Gemini API key? =

For free at https://aistudio.google.com/apikey with a Google account.

= How do I make sure I never get charged? =

Set the "Total daily limit" in the settings to a value conservatively below your actual free quota (check it at https://aistudio.google.com/rate-limit, it changes over time and per account). The plugin stops itself once the cap is reached and resumes the next day: it can never exceed that number of calls, so it can never generate costs as long as you stay within Google's free tier.

= What happens when the daily limit is reached? =

The form keeps working as a normal contact form: the notification email still arrives, and the visitor sees a configurable message instead of the AI report.

= Do I need a paid Google Cloud account? =

No, the Gemini free tier does not require a credit card.

== External services ==

This plugin connects to the Google Gemini API (endpoint: https://generativelanguage.googleapis.com) to generate the feasibility report shown to the visitor after the form is submitted.

* **When**: only when a visitor submits the form and the configured daily limit has not been reached.
* **What data is sent**: the name/company, the selected service and the project description entered by the visitor in the form. The email address is NOT sent to Google.
* **Authentication**: your personal Google AI Studio API key, configured in the plugin settings.
* If the API key is not configured or the request fails, no data is processed by Gemini: the form still works as a normal contact form and only sends the notification email.

Google Terms of Service: https://policies.google.com/terms
Gemini API Terms: https://ai.google.dev/gemini-api/terms
Google Privacy Policy: https://policies.google.com/privacy

== Changelog ==

= 1.0.2 =
* New: the form's JavaScript now dispatches public events (`mcag:submit`, `mcag:result`, `mcag:error`) so themes and pages can show loading and result states in custom panels.

= 1.0.1 =
* New: mandatory data-processing consent checkbox (GDPR), validated both client-side and server-side.

= 1.0.0 =
* Initial release: contact form, AI Gemini report, global daily limit, per-IP rate limit, anti-spam honeypot.
