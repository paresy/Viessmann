<?php

declare(strict_types=1);
include_once __DIR__ . '/stubs/Validator.php';
class ViessmannValidationTest extends TestCaseSymconValidation
{
    public function testValidateViessmann(): void
    {
        $this->validateLibrary(__DIR__ . '/..');
    }
    public function testValidateVitoConnectModule(): void
    {
        $this->validateModule(__DIR__ . '/../VitoConnect');
    }
}