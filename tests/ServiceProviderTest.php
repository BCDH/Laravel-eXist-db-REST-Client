<?php

namespace {
    if (!function_exists('config_path')) {
        function config_path($path = '') {
            $basePath = __DIR__;

            return $path === '' ? $basePath : $basePath . DIRECTORY_SEPARATOR . $path;
        }
    }
}

namespace BCDH\ExistDbRestClient {

    use ArrayAccess;
    use Illuminate\Support\ServiceProvider;
    use PHPUnit\Framework\TestCase;

    class ServiceProviderTest extends TestCase {
        protected function setUp(): void {
            $this->resetPublishedPaths();
        }

        public function testRegisterMergesPackageConfig() {
            $app = new FakeApplication();
            $provider = new ExistDbServiceProvider($app);

            $provider->register();

            $config = $app['config']->get('exist-db');

            $this->assertSame('admin', $config['user']);
            $this->assertSame('exist/rest', $config['path']);
            $this->assertSame('yes', $config['wrap']);
        }

        public function testRegisterPreservesExistingConfigValues() {
            $app = new FakeApplication([
                'exist-db' => [
                    'user' => 'custom-user',
                    'wrap' => 'no',
                ],
            ]);
            $provider = new ExistDbServiceProvider($app);

            $provider->register();

            $config = $app['config']->get('exist-db');

            $this->assertSame('custom-user', $config['user']);
            $this->assertSame('no', $config['wrap']);
            $this->assertSame('exist/rest', $config['path']);
        }

        public function testBootRegistersPublishableConfig() {
            $app = new FakeApplication();
            $provider = new ExistDbServiceProvider($app);

            $provider->boot();

            $publishes = $this->getServiceProviderStaticProperty('publishes');
            $publishGroups = $this->getServiceProviderStaticProperty('publishGroups');
            $providerClass = ExistDbServiceProvider::class;
            $sourcePath = dirname(__DIR__) . '/config/exist-db.php';

            $this->assertArrayHasKey($providerClass, $publishes);
            $this->assertSame(
                [__DIR__ . '/exist-db.php'],
                array_values($publishes[$providerClass])
            );
            $this->assertArrayHasKey('config', $publishGroups);
            $this->assertArrayHasKey($sourcePath, $publishGroups['config']);
            $this->assertSame(__DIR__ . '/exist-db.php', $publishGroups['config'][$sourcePath]);
        }

        private function resetPublishedPaths() {
            $this->setServiceProviderStaticProperty('publishes', []);
            $this->setServiceProviderStaticProperty('publishGroups', []);
        }

        private function getServiceProviderStaticProperty($property) {
            $reflection = new \ReflectionClass(ServiceProvider::class);
            $reflectionProperty = $reflection->getProperty($property);
            $reflectionProperty->setAccessible(true);

            return $reflectionProperty->getValue();
        }

        private function setServiceProviderStaticProperty($property, $value) {
            $reflection = new \ReflectionClass(ServiceProvider::class);
            $reflectionProperty = $reflection->getProperty($property);
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue(null, $value);
        }
    }

    class FakeApplication implements ArrayAccess {
        private $items;

        public function __construct($config = []) {
            $this->items = [
                'config' => new FakeConfigRepository($config),
            ];
        }

        public function configurationIsCached() {
            return false;
        }

        public function offsetExists($offset) {
            return isset($this->items[$offset]);
        }

        public function offsetGet($offset) {
            return $this->items[$offset];
        }

        public function offsetSet($offset, $value) {
            $this->items[$offset] = $value;
        }

        public function offsetUnset($offset) {
            unset($this->items[$offset]);
        }
    }

    class FakeConfigRepository {
        private $items;

        public function __construct($items = []) {
            $this->items = $items;
        }

        public function get($key, $default = null) {
            if (!isset($this->items[$key])) {
                return $default;
            }

            return $this->items[$key];
        }

        public function set($key, $value) {
            $this->items[$key] = $value;
        }
    }
}
