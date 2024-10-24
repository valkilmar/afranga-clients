<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Jobs\ClientExportCleaner;
use Spatie\SimpleExcel\SimpleExcelWriter;
use App\Models\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ClientExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const DEFAULT_CHUNK_SIZE = 10;

    const DEFAULT_EXPIRE_AFTER = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public $exportId,
        public $chunkSize = self::DEFAULT_CHUNK_SIZE
    )
    {  
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $part = 1;
        
        $writer = null;

        $filePath = null;

        $this->initExportStatus();
        
        $query = Client::orderBy('name', 'asc')->with('phones');

        foreach ($query->lazy($this->chunkSize) as $index => $client) {

            if (is_null($writer)) {

                $filePath = $this->getTargetFilePath($part);

                $writer = SimpleExcelWriter::create(Storage::path($filePath));

                $writer->addHeader(['NAME', 'PERSONAL NO.', 'CARD NO.', 'PHONES']);
            }

            $writer->addRow($client->toArrayForExport());

            if ( ($index > 0) && ($index % $this->chunkSize === 0) ) {

                $writer->close();

                $this->updateExportStatus($filePath, false);

                $writer = null;

                $part++;
            }
        }

        if (is_null($writer)) {

            $this->updateExportStatus(null, true);

        } else {

            $writer->close();

            $this->updateExportStatus($filePath, true);

        }

        // Handle garbage collection
        ClientExportCleaner::dispatch($this->exportId)->delay(self::DEFAULT_EXPIRE_AFTER);
    }


    public static function getExportStatus($exportId) {

        if (!$exportId) {
            return null;
        }

        return Storage::json(self::getStatusFilePath($exportId));

    }


    public static function checkExportStatus($exportId) {

        $status = self::getExportStatus($exportId);

        if (!$status || empty($status['files'])) {
            return null;
        }

        if ($status['timeDone'] > 0) {
            $timeLeft = $status['expireAfter'] - (time() - $status['timeDone']);
            
            $timeLeft = max(0, $timeLeft);

            if ($timeLeft === 0) {
                return null;
            }

            $status['timeLeft'] = $timeLeft;

            Storage::put(self::getStatusFilePath($exportId), json_encode($status));
        }

        return $status;
    }


    private function initExportStatus() {
        $status = [
            'files' => [],
            'expireAfter' => self::DEFAULT_EXPIRE_AFTER,
            'timeLeft' => -1,
            'timeDone' => -1
        ];

        $filePath = self::getStatusFilePath($this->exportId);

        Storage::put($filePath, json_encode($status));
    }


    private function updateExportStatus($fileNameReady = null, $done = false) {

        $status = self::getExportStatus($this->exportId);

        if (!$status) {
            return;
        }

        if ($fileNameReady) {

            if (($pos = strrpos($fileNameReady, "/")) !== false) {

                $fileNameReady = substr($fileNameReady, $pos + 1);
    
            }

            $url = Storage::temporaryUrl(
                "{$this->exportId}/{$fileNameReady}", now()->addSeconds(self::DEFAULT_EXPIRE_AFTER)
            );

            $status['files'][$fileNameReady] = $url;

        }
        if ($done) {

            $status['timeDone'] = time();

        }

        Storage::put(self::getStatusFilePath($this->exportId), json_encode($status));
    }


    private function getTargetFilePath(int $part) {

        $timestamp = date('Ymd');

        $partFormatted = sprintf("%'.03d", $part);

        $filePath = "{$this->exportId}/export_{$timestamp}_{$partFormatted}.xlsx";

        Storage::put($filePath, '');

        return $filePath;
    }


    public static function getStatusFilePath($exportId) {

        return "{$exportId}/status.json";

    }
}
