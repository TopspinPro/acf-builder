<?php

require dirname(__DIR__) . '/vendor/autoload.php';

if (!class_exists('PHPUnit_Framework_TestCase')) {
    abstract class PHPUnit_Framework_TestCase extends \PHPUnit\Framework\TestCase
    {
        public static function assertInternalType(string $expectedType, $actual, string $message = ''): void
        {
            self::assertThat($actual, new \PHPUnit\Framework\Constraint\IsType($expectedType), $message);
        }

        public function setExpectedException(string $exception): void
        {
            $this->expectException($exception);
        }

        public static function assertArraySubset($subset, $array, bool $checkForIdentity = false, string $message = ''): void
        {
            $constraint = new class($subset, $checkForIdentity) extends \PHPUnit\Framework\Constraint\Constraint {
                private array $subset;
                private bool $checkForIdentity;

                public function __construct(array $subset, bool $checkForIdentity)
                {
                    $this->subset = $subset;
                    $this->checkForIdentity = $checkForIdentity;
                }

                protected function matches($other): bool
                {
                    if (!is_array($other)) {
                        return false;
                    }

                    return $this->isSubset($this->subset, $other);
                }

                private function isSubset(array $subset, array $array): bool
                {
                    foreach ($subset as $key => $value) {
                        if (!array_key_exists($key, $array)) {
                            return false;
                        }

                        if (is_array($value)) {
                            if (!is_array($array[$key]) || !$this->isSubset($value, $array[$key])) {
                                return false;
                            }

                            continue;
                        }

                        if ($this->checkForIdentity ? $array[$key] !== $value : $array[$key] != $value) {
                            return false;
                        }
                    }

                    return true;
                }

                public function toString(): string
                {
                    return 'has the expected array subset';
                }
            };

            self::assertThat($array, $constraint, $message);
        }
    }
}
