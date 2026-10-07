# AI Provider for Magnitude

Text generation with tool calling and image input on supported models, running locally on your own computer with [Magnitude](https://magnitude.dev/).

This WordPress plugin is a provider connector for the **WordPress AI Client** (WordPress 7.0+). It connects your site to a Magnitude server, so any plugin that uses the AI Client can run its prompts on an open-weight model on your own hardware, with no cloud API key.

> This is an independent project. It is not affiliated with, endorsed by, or sponsored by Magnitude AI Inc.

## How it works

1. The plugin registers Magnitude with the AI Client. WordPress then lists it on **Settings > Connectors** by itself.
2. It reads the models Magnitude is running through Magnitude's OpenAI-compatible API (`/inference/v1`).
3. Each model's features follow what Magnitude reports for it. When you download and start a new model, it gets its tools, structured output and image input automatically, with no plugin update.

## Requirements

- WordPress 7.0 or later
- PHP 7.4 or later
- The [Magnitude](https://magnitude.dev/) app, running with at least one model started. Magnitude lists only models that are running.

## Installation

1. Copy the plugin folder to `wp-content/plugins/ai-provider-for-magnitude/`, or upload the release zip on **Plugins > Add New Plugin > Upload Plugin**.
2. Activate **AI Provider for Magnitude**.
3. Open **Settings > Magnitude**. The status should read **Connected**, and your running models are listed.

No API key is needed when Magnitude runs on the same computer as WordPress.

## Settings

**Settings > Magnitude** shows the connection status, the server address, and the models Magnitude is running. **Check connection** asks Magnitude again.

The default address is `http://127.0.0.1:10100/inference`.

### Use Magnitude on another computer

1. In the Magnitude app on that computer, turn on **Network access**.
2. If **Require API key** is on, copy the key and enter it for Magnitude on **Settings > Connectors**.
3. Copy the address under **Reachable at**, paste it on **Settings > Magnitude** and save. The plugin adds the `/v1` part itself, so either form works.

Only use this on a network you trust.

### Fix the address in code

Add this to `wp-config.php`. The settings field is then locked.

```php
define( 'AI_PROVIDER_FOR_MAGNITUDE_HOST', 'http://192.168.1.20:10100/inference' );
```

Only `http` and `https` addresses with a host and no username or password are accepted. Anything else falls back to the default.

## What is supported

| Feature | Status |
| --- | --- |
| Text generation, system instructions, chat history | Supported |
| Tool (function) calling | Supported on models that report it |
| Structured JSON output | Supported on models that report it |
| Image input (vision) | Offered on models that report it |
| Audio input | Offered on models that report it |
| Several answers per request | Not available. Magnitude returns one. |
| Image generation, embeddings, speech | Not available. Magnitude does not offer them. |

Some models think before they answer. Give those a generous token limit, or they can run out of tokens before the reply.

## Hooks

| Hook | Type | Purpose |
| --- | --- | --- |
| `ai_provider_for_magnitude_host` | Filter | Change the server address. Invalid values fall back to the default. |
| `ai_provider_for_magnitude_request_timeout` | Filter | Change the request timeout in seconds. Default `120`. |

## Development

The plugin has no runtime dependencies. The AI Client ships with WordPress core.

```
includes/
  Admin/        Plugin list links
  Connection/   Connection status and inspector
  Contracts/    Interfaces
  Host/         Server address resolver
  Http/         Allows the configured local host
  Integration/  Optional WordPress AI plugin integration
  Metadata/     Model list and capability mapping
  Models/       Text generation model
  Provider/     Provider and its registration
  Settings/     Settings screen and address validation
  Traits/       Shared helpers
assets/css/     Settings screen styles
tests/Unit/     PHPUnit tests
```

Install the dev tools once, then use the composer scripts:

```bash
composer install
composer lint      # WordPress Coding Standards, PHP 7.4 compatibility
composer phpstan   # static analysis, level 8
composer test      # PHPUnit unit tests
composer format    # fix coding standard issues automatically
```

Tooling is configured in `phpcs.xml.dist`, `phpstan.neon.dist` and `phpunit.xml.dist`. The unit tests and PHPStan load the AI Client from the WordPress install that contains the plugin, so keep the plugin in `wp-content/plugins`. Set `AI_MAGNITUDE_ABSPATH` to your WordPress root if it lives somewhere else (tests only).

To build a release, copy the plugin without the files listed in `.distignore`, then run the [Plugin Check](https://wordpress.org/plugins/plugin-check/) plugin on the result.

## License

GPL-2.0-or-later. See `readme.txt` for the WordPress.org description and changelog.
