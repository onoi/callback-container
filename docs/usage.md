## Usage

### Using the CallbackContainer

```php
class FooCallbackContainer implements CallbackContainer {

	public function register( ContainerBuilder $containerBuilder ) {
		$this->addCallbackHandlers( $containerBuilder);
	}

	private function addCallbackHandlers( $containerBuilder ) {

		$containerBuilder->registerCallback( 'Foo', function( ContainerBuilder $containerBuilder, array $input ) {
			$containerBuilder->registerExpectedReturnType( 'Foo', '\stdClass' );

			$stdClass = new \stdClass;
			$stdClass->input = $input;

			return $stdClass;
		} );
	}
}
```
```php
use Onoi\CallbackContainer\CallbackContainerFactory;

$callbackContainerFactory = new CallbackContainerFactory();
$containerBuilder = $callbackContainerFactory->newCallbackContainerBuilder();

$containerBuilder->registerCallbackContainer( new FooCallbackContainer() );

$instance = $containerBuilder->create(
	'Foo',
	array( 'a', 'b' )
);

$instance = $containerBuilder->singleton(
	'Foo',
	array( 'aa', 'bb' )
);
```

### Using a service file

```php
return array(

	/**
	 * @return Closure
	 */
	'SomeServiceFromFile' => function( $containerBuilder ) {
		return new \stdClass;
	},

	/**
	 * @return Closure
	 */
	'AnotherServiceFromFile' => function( $containerBuilder, $argument1, $argument2 ) {
		$containerBuilder->registerExpectedReturnType( 'AnotherServiceFromFile', '\stdClass' )

		$service = $containerBuilder->create( 'SomeServiceFromFile' );
		$service->argument1 = $argument1;
		$service->argument2 = $argument2;

		return $service;
	}
);
```
```php
use Onoi\CallbackContainer\CallbackContainerFactory;

$callbackContainerFactory = new CallbackContainerFactory();
$containerBuilder = $callbackContainerFactory->newCallbackContainerBuilder();

$containerBuilder->registerFromFile( __DIR__ . '/Foo.php' );
$someServiceFromFile = $containerBuilder->create( 'SomeServiceFromFile' );
$anotherServiceFromFile = $containerBuilder->create( 'AnotherServiceFromFile', 'Foo', 'Bar' );

```

### ContainerRegistry::registerExpectedReturnType

If a callback handler is registered with an expected return type then any
mismatch of a returning instance will throw a `ServiceTypeMismatchException`.
