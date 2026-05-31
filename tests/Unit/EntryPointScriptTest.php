<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class EntryPointScriptTest extends TestCase
{
    public function test_entrypoint_does_not_force_regenerate_existing_app_key(): void
    {
        $script = file_get_contents(__DIR__.'/../../docker/app/entrypoint.sh');

        $this->assertIsString($script);
        $this->assertStringContainsString('APP_KEY already configured; leaving $ENV_FILE unchanged.', $script);
        $this->assertStringContainsString('php artisan key:generate --force --no-interaction', $script);
        $this->assertStringNotContainsString("grep -q '^APP_KEY=base64:'", $script);
    }

    public function test_entrypoint_rejects_concatenated_app_keys(): void
    {
        $script = file_get_contents(__DIR__.'/../../docker/app/entrypoint.sh');

        $this->assertIsString($script);
        $this->assertStringContainsString('base64_count', $script);
        $this->assertStringContainsString('corrupted APP_KEY with multiple base64: prefixes', $script);
    }

    public function test_docker_compose_does_not_override_application_env_values(): void
    {
        $compose = file_get_contents(__DIR__.'/../../docker-compose.yml');

        $this->assertIsString($compose);
        $this->assertStringContainsString("env_file:\n      - .env", $compose);
        $this->assertStringNotContainsString('APP_KEY:', $compose);
        $this->assertStringNotContainsString('APP_ENV: ${APP_ENV', $compose);
        $this->assertStringNotContainsString('DB_HOST: ${DB_HOST', $compose);
        $this->assertStringNotContainsString('SCOUT_DRIVER: ${SCOUT_DRIVER', $compose);
    }

    #[DataProvider('appKeyProvider')]
    public function test_app_key_pattern_expectations(string $value, bool $valid, bool $corrupted): void
    {
        $base64Count = substr_count($value, 'base64:');
        $matches = $value === '' || preg_match('/^base64:[A-Za-z0-9+\/=]+$/', $value) === 1;

        $this->assertSame($corrupted, $base64Count > 1);
        $this->assertSame($valid, $matches && ! $corrupted);
    }

    public static function appKeyProvider(): array
    {
        return [
            'empty key can be generated once' => ['', true, false],
            'single valid key is preserved' => ['base64:MTIzNDU2Nzg5MDEyMzQ1Njc4OTAxMjM0NTY3ODkwMTI=', true, false],
            'concatenated key is corrupt' => ['base64:abc=base64:def=', false, true],
            'plain text key is invalid' => ['not-a-base64-key', false, false],
        ];
    }
}
