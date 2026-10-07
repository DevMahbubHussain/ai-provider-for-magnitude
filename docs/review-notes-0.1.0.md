# AI Provider for Magnitude 0.1.0 — notes for the plugin review team

| | |
| --- | --- |
| Plugin name | AI Provider for Magnitude |
| Requested slug | `ai-provider-for-magnitude` |
| Version | 0.1.0 (first release) |
| WordPress.org username | `mahbubwpwist` |
| Author | Mahbub Hussain |
| License | GPL-2.0-or-later |
| Requires | WordPress 7.0, PHP 7.4 |
| Source repository | https://github.com/DevMahbubHussain/ai-provider-for-magnitude |
| Zip SHA-256 | `60816057c05dd042be6f016e7cc3b10527b161c12c65de9eadff295caec580dd` |

## 1. What the plugin does

WordPress 7.0 includes the **AI Client** and the **Connectors API**. A provider plugin registers a provider with the AI Client, and WordPress then lists it on Settings > Connectors by itself.

This plugin registers one provider, **Magnitude** (https://magnitude.dev/), an open-source inference engine that runs open-weight models on the user's own computer. Magnitude exposes an OpenAI-compatible API. With this plugin, any plugin that uses `wp_ai_client_prompt()` can run its prompts on a local Magnitude model, with no cloud account and no API key.

Supported: text generation, system instructions, chat history, tool (function) calling, structured JSON output, and image or audio input on models that report them. Capabilities come from what Magnitude reports for each model, so nothing is hardcoded.

Not supported, and not claimed anywhere: image generation, embeddings, speech, and more than one answer per request. Magnitude does not offer them (its `/v1/images/generations`, `/v1/embeddings` and `/v1/audio/speech` endpoints return 404).

## 2. How it fits WordPress's AI architecture

- It uses the AI Client that ships in core. **No library is bundled** and there is no `vendor` folder in the release.
- It extends core's own classes (`AbstractApiProvider`, `AbstractOpenAiCompatibleTextGenerationModel`, `AbstractOpenAiCompatibleModelMetadataDirectory`) and overrides only what Magnitude does differently. Two server differences are handled in the model class: Magnitude needs `system` and `assistant` message content as a plain string, and a `name` inside `response_format.json_schema`.
- It registers on `init` through `AiClient::defaultRegistry()->registerProvider()`. The Connectors screen entry is created by core from the provider metadata. The plugin adds no connector registration of its own.
- Credentials are left to core. The plugin does not store keys. A key entered on Settings > Connectors reaches Magnitude as a Bearer token (verified). Without a key, an empty fallback is set at `init` priority 15, before core applies a saved key at priority 20.
- Model features are advertised only when Magnitude reports them. The plugin declares that only one answer per request is supported, because Magnitude rejects `n` greater than 1.

## 3. External service disclosure (Guidelines 6 and 7)

The plugin contacts **only the Magnitude server address that the site owner configures**. By default that is `http://127.0.0.1:10100/inference`, a program the user runs on the same computer. The address can be changed on Settings > Magnitude or with the `AI_PROVIDER_FOR_MAGNITUDE_HOST` constant.

- **When requests are sent:** when another plugin uses the AI Client with this provider, when an administrator opens Settings > Connectors or Settings > Magnitude (to list models), and when the administrator presses "Check connection". Nothing is sent on activation, on the front end, or in the background.
- **What is sent:** the prompt and context supplied by the plugin that is using the AI Client, model list requests, and the API key if the administrator entered one.
- **What is not done:** no telemetry, no analytics, no update checks, no remote code or assets, no data sent to the plugin author or to any third party.
- **Disclosure:** the readme has an "External services" section stating the above, with a link to Magnitude.

Magnitude is a separate program. The site owner chooses to install and run it.

## 4. Data stored

One option, `ai_provider_for_magnitude_host`, holding the server address. `uninstall.php` deletes it, on every site of a multisite network. Nothing else is stored.

## 5. Security measures

- **Capabilities:** the settings screen and the connection check require `manage_options`, checked again in the handlers.
- **Nonces:** the settings form uses the Settings API (`settings_fields()`); the "Check connection" action uses `check_admin_referer()`. Verified with real requests: no nonce returns 403, and a logged-out request is refused.
- **Input:** the server address goes through a validator. It must start with `http://` or `https://`, have a host, and have no credentials, query string, fragment, spaces, quotes or angle brackets. Invalid input keeps the saved value and shows an error. The same validation covers the constant and the filter, so a bad value falls back to the default.
- **Output:** everything is escaped where it is printed (`esc_html`, `esc_attr`, `esc_url`).
- **Server responses are treated as untrusted:** their shape is checked before use, and an unreachable server shows a generic message, never the raw exception.
- **Network scope:** WordPress blocks requests to local and private addresses by default. The plugin allows only the configured host, and the extra port only for that host, not globally.
- **No SQL, no file writes, no uploads, no `eval`, no obfuscation.** All files start with an `ABSPATH` guard.

## 6. Standards and checks run on the release zip

The zip was extracted and checked, and its contents are byte-identical to the committed source.

| Check | Result |
| --- | --- |
| Plugin Check 2.1.0, all categories, including experimental checks | No errors, no warnings |
| PHP_CodeSniffer: WordPress, WordPress-Docs, WordPress-Extra | Clean |
| PHP_CodeSniffer: WordPress-VIP-Go | Clean |
| PHPCompatibilityWP for PHP 7.4 and later | Clean |
| PHPStan, level 8 (configured) and level 9 (run manually) | No errors |
| stylelint with `@wordpress/stylelint-config` | Clean |
| PHPUnit | 53 tests, 76 assertions, passing |

Live testing on WordPress 7.1.3 against Magnitude 0.2.6 with the model MiniCPM5 1B (Q4): text generation, system instruction, JSON-schema output, chat history, and a complete tool-calling round trip (the model calls a tool, receives the result, and answers). The settings screen was also tested with a real authenticated session: saving a valid address, rejecting invalid ones, clearing the setting, and the connection check, each showing exactly one notice.

PHP 7.4 compatibility was checked with static analysis (PHPCompatibilityWP). The tests ran on PHP 8.4, not on a PHP 7.4 runtime.

## 7. Guidelines

- **1 GPL:** every file is GPL-2.0-or-later and `LICENSE` is included. No third-party code or assets are shipped.
- **3, 15, 16:** a complete, working first release, with the version in the header, the readme and the code constant checked to match.
- **4 Human-readable code:** plain PHP and one plain CSS file. No minified or generated code.
- **5, 10, 11, 12:** no paid or locked features, no credits, no upsells, no dashboard notices. The readme uses 5 tags.
- **8, 13:** no external code or CDN assets, and no bundled copy of a library that WordPress provides.
- **17 Trademarks:** the name follows the "for [brand]" form. The plugin is described as independent and not affiliated with or endorsed by Magnitude AI Inc., in both the readme and the repository README. **No Magnitude logo or other brand artwork is shipped**, so the connector uses core's generic icon. I have contacted Magnitude AI Inc. by email about the name and will follow their answer.

## 8. Design choices a reviewer may ask about

- **File names** follow the class names (PSR-4), for example `includes/Settings/Settings_Page.php`, not `class-*.php`. This matches how the WordPress AI plugin is organised. The coding standard's file-name sniff is turned off for that reason only. Plugin Check does not flag it.
- **A settings page exists** (Settings > Magnitude). It holds only the non-secret server address, the connection status and the running models. Credentials stay on Settings > Connectors. It loads one stylesheet, only on its own screen, and no JavaScript.
- **The provider reads the address through a small resolver object** instead of an instance, because the AI Client creates providers through static methods.
- **Global names** use the prefix `ai_provider_for_magnitude_` and the `AiProviderForMagnitude` namespace. The two filters are `ai_provider_for_magnitude_host` and `ai_provider_for_magnitude_request_timeout`.
- **An optional integration** with the WordPress AI feature plugin (`wpai_has_ai_credentials`) reports Magnitude as available while it is registered. It does nothing when that plugin is not installed.

## 9. How to test

**Without installing anything else**

1. Activate the plugin on WordPress 7.0 or later.
2. Open Settings > Connectors. Magnitude is listed with core's generic icon.
3. Open Settings > Magnitude. With no Magnitude running, the status reads "Not reachable" with a plain message and no error.

**With Magnitude**

1. Install the Magnitude app from https://magnitude.dev/ and let it finish its first start.
2. Download a small model, for example "MiniCPM5 1B (Q4)" (about 0.7 GB), and start it. Magnitude lists only models that are running.
3. Reload Settings > Magnitude. The status reads "Connected" and the model is listed with its capabilities.
4. Run a prompt, for example with WP-CLI:

```
wp eval 'echo wp_ai_client_prompt( "Say hello to WordPress in one short sentence." )->using_provider( "magnitude" )->using_max_tokens( 600 )->generate_text();'
```

Some models think before they answer, so a generous `max_tokens` is needed, as in the example.

## 10. Known limitations

- Magnitude must be installed and running separately. Models appear only while running, and the first request to a model can take about a minute while it loads.
- One answer per request.
- An unreachable host that does not answer at all takes about 5 seconds before the screen reports "Not reachable". A refused connection is instant.
- No automated tests run inside a full WordPress install. The WordPress behaviour was tested by hand against a running site.

Thank you for reviewing. I will answer any question or change request promptly.
