<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class)->in('Feature');
uses(TestCase::class, RefreshDatabase::class)->in('Unit');

require_once __DIR__.'/Helpers/AdminTestContext.php';
require_once __DIR__.'/Helpers/RoleTestContext.php';
