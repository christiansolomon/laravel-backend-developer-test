<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Product;
use App\Services\SpreadsheetService;
use App\Jobs\ProcessProductImage;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SpreadsheetServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_process_spreadsheet_creates_valid_products()
    {
        // Fake the job queue
        Queue::fake();

        // Mock the importer
        $data = [
            ['product_code' => 'P001', 'quantity' => 10],
            ['product_code' => 'P002', 'quantity' => 5],
        ];

        app()->bind('importer', function () use ($data) {
            return new class($data) {
                public function __construct(private $data) {}
                public function import($filePath) {
                    return $this->data;
                }
            };
        });

        // Run the service
        $service = new SpreadsheetService();
        $service->processSpreadsheet('ignored-because-importer-is-mocked.csv');

        // Assert products created
        $this->assertDatabaseCount('products', 2);
        $this->assertDatabaseHas('products', ['code' => 'P001']);
        $this->assertDatabaseHas('products', ['code' => 'P002']);

        // Assert jobs dispatched
        Queue::assertPushed(ProcessProductImage::class, 2);
    }

    public function test_process_spreadsheet_skips_invalid_rows()
    {
        // Fake the queue
        Queue::fake();

        $data = [
            ['product_code' => '', 'quantity' => 10], // Invalid: missing code
            ['product_code' => 'P004', 'quantity' => 0], // Invalid: quantity < 1
        ];

        app()->bind('importer', function () use ($data) {
            return new class($data) {
                public function __construct(private $data) {}
                public function import($filePath) {
                    return $this->data;
                }
            };
        });

        $service = new SpreadsheetService();
        $service->processSpreadsheet('ignored-because-importer-is-mocked.csv');

        // Assert nothing was created or dispatched
        $this->assertDatabaseCount('products', 0);
        Queue::assertNothingPushed();
    }

    public function test_process_spreadsheet_with_empty_file()
    {
        Queue::fake();

        app()->bind('importer', function () {
            return new class {
                public function import($filePath) {
                    return []; // Empty file
                }
            };
        });

        $service = new SpreadsheetService();
        $service->processSpreadsheet('ignored-because-importer-is-mocked.csv');

        $this->assertDatabaseCount('products', 0);
        Queue::assertNothingPushed();
    }
}


/* Based on the method, we should test:
1. It creates products when the spreadsheet has valid data.

2. It skips invalid rows (e.g missing product_code or non-integer quantity).

3. It dispatches the ProcessProductImage job for each valid product.

4. It doesn't throw errors if the file is empty.
*/