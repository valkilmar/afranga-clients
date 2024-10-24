<?php

use Illuminate\Support\Facades\Route;
use App\Models\Client;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/export/get/{path}', function (string $path){
//     return Storage::disk('local')->download($path);
// })->name('export-download');

Route::controller(App\Http\Controllers\ClientController::class)
    ->middleware([])
    ->group(function () {

        Route::get('/edit/{client?}', 'edit')->name('client-edit');
        
        Route::get('/delete/{client}', 'delete')->name('client-delete');
        
        Route::get('/export/get/{exportId}/{path}', 'download')->name('client-export-download');

        Route::get('/export/{chunkSize?}', 'export')->name('client-export');

        Route::post('/create', 'create')->name('client-create');

        Route::post('/update', 'update')->name('client-update');

        Route::get('/', 'index')->name('client-index');
    });
