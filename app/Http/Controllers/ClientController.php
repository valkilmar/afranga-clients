<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use App\Http\Requests\ClientSaveRequest;
use App\Jobs\ClientExport;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;


class ClientController extends Controller
{

    /**
     * Return clients index (with phones) using cursorPaginator
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index() {

        $limit = request('limit', 10);

        $paginator = Client::orderBy('updated_at', 'desc')
            ->with('phones')
            ->cursorPaginate($limit);

        return view('client-index', [
                'clients' => $paginator->items(),
                'limit' => $limit,
                'next' => $paginator->hasMorePages() ? $paginator->nextPageUrl() : null,
                'prev' => $paginator->onFirstPage() ? null : $paginator->previousPageUrl(),
                'exportStatus' => ClientExport::checkExportStatus(session('exportId', ''))
            ]);

    }


    /**
     * Render create/edit client form
     *
     * @param Request $request
     * @param Client|null $client
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Request $request, Client $client = null) {

        return view('client-edit', [
                'client' => $client
            ]);
    }


    /**
     * Update existing client 
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(ClientSaveRequest $request) {

        $validated = $request->validated();

        $clientId = $request->input('client.id');

        $actionName = 'update';

        $client = Client::with('phones')->find($clientId);

        $client->fill($validated['client']);

        if (!$client->isDirty()) {

            $message = $this->formatMessage('success', "No changes made for {$client->name}");

            return redirect()->route('client-index')->with('message', $message);    
        }

        try {
            if ($client->push()) {

                $message = $this->formatMessage('success', "{$client->name} {$actionName}d successfully.");

            } else {

                $message = $this->formatMessage('danger', "Unable to {$actionName} {$client->name}.");

            }
        } catch (\Exception $ex) {

            $message = $this->formatMessage('danger', "Unable to {$actionName} {$client->name}. Try again later.");

        }

        return redirect()->route('client-index')->with('message', $message);
    }


    /**
     * Create new client 
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(ClientSaveRequest $request) {

        $validated = $request->validated();

        $actionName = 'create';

        $client = new Client();

        $client->fill($validated['client']);

        try {
            if ($client->push()) {

                $message = $this->formatMessage('success', "{$client->name} {$actionName}d successfully.");

            } else {

                $message = $this->formatMessage('danger', "Unable to {$actionName} {$client->name}.");

            }
        } catch (\Exception $ex) {
            $message = $this->formatMessage('danger', "Unable to {$actionName} {$client->name}. Try again later.");
        }

        return redirect()->route('client-index')->with('message', $message);
    }



    /**
     * Delete client (and phones)
     *
     * @param Request $request
     * @param Client $client
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(Request $request, Client $client) {
        try {
            $clientName = $client->name;

            $client->delete();

            $message = $this->formatMessage('success', "{$clientName} deleted successfully.");

        } catch (\Exception $e) {

            $message = $this->formatMessage('danger', $e->getMessage());

        }
        
        return redirect()->route('client-index')->with('message', $message);
    }



    /**
     * Initiate export of clients list to xlsx
     *
     * @param Request $request
     * @return void
     */
    public function export(Request $request, string $chunkSize = null) {

        $exportId = Str::random(10);

        session()->put('exportId', $exportId);

        $chunkSize ??= ClientExport::DEFAULT_CHUNK_SIZE;

        ClientExport::dispatch(
            $exportId,
            $chunkSize
        );

        $message = $this->formatMessage('success', "Exporting {$chunkSize} per file started... Download links coming soon.");

        return response()->json(['message' => $message]);
        // return redirect()->route('client-index')->with('message', $message);
    }

    

    /**
     * Doanload exported file
     *
     * @param string $exportId
     * @param string $path
     * @param Request $request
     * @return void
     */
    public function download(Request $request, string $exportId, string $path) {

        try {
            $res = Storage::disk('local')->download($exportId . '/' . $path);
            return $res;
        } catch (\Exception $ex) {
            $message = $this->formatMessage('danger', "That file is expired. You can try with a fresh export.");

            return redirect()->route('client-index')->with('message', $message);
        }
    }


    /**
     * Export clients list to csv/xls/xlsx
     *
     * @param Request $request
     * @return void
     */
    public function exportTest(Request $request) {

        // header("Content-type: application/csv");
        // $writer = SimpleExcelWriter::create("clients.csv");

        
        $chunkSize = 35;
        
        $query = Client::orderBy('updated_at', 'desc')->with('phones');
        
        $i = 0;
        
        $part = 1;

        $writer = SimpleExcelWriter::streamDownload("clients_{$part}.csv");

        foreach ($query->lazy($chunkSize) as $client) {

            $writer->addRow($client->toArrayForExport());

            if ($i % $chunkSize === 0) {
                flush(); // Flush the buffer every 1000 rows
                $writer->toBrowser();
                $writer->close();

                $part++;
                $writer = SimpleExcelWriter::streamDownload("clients_{$part}.csv");
            }

            $i++;
        }

        flush(); // Flush the buffer every 1000 rows
        $writer->toBrowser();
        $writer->close();

        $message = $this->formatMessage('success', 'Link to download export comming soon...');
        return redirect()->route('client-index')->with('message', $message);
    }



    /**
     * Format a flash message
     *
     * @param string $messageClass
     * @param string $message
     * @return string
     */
    private function formatMessage(string $messageClass, string $message) {
        return "{$messageClass}|{$message}";
    } 
}
