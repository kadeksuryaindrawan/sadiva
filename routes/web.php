<?php

use App\Http\Controllers\{
    DashboardController,
    KategoriController,
    LaporanController,
    ProdukController,
    PembelianController,
    PembelianDetailController,
    PermintaanPembelianController,
    PermintaanPembelianDetailController,
    PenjualanController,
    PenjualanDetailController,
    SettingController,
    SupplierController,
    UserController,
};
use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return redirect()->route('login');
});

Route::group(['middleware' => 'auth'], function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::group(['middleware' => 'level:1'], function () {
        Route::get('/kategori/data', [KategoriController::class, 'data'])->name('kategori.data');
        Route::resource('/kategori', KategoriController::class);

        Route::get('/supplier/data', [SupplierController::class, 'data'])->name('supplier.data');
        Route::resource('/supplier', SupplierController::class);

        Route::get('/kasir/data', [UserController::class, 'dataKasir'])->name('kasir.data');
        Route::get('/halaman-kasir', [UserController::class, 'indexKasir'])->name('indexKasir');
        Route::post('/tambah-kasir', [UserController::class, 'storeKasir'])->name('tambahKasir');
        Route::resource('/kasir', UserController::class);


        Route::get('/gudang/data', [UserController::class, 'dataGudang'])->name('gudang.data');
        Route::get('/halaman-gudang', [UserController::class, 'indexGudang'])->name('indexGudang');
        Route::post('/tambah-gudang', [UserController::class, 'storeGudang'])->name('tambahGudang');
        Route::resource('/gudang', UserController::class);

        Route::get('/pemilik/data', [UserController::class, 'dataPemilik'])->name('pemilik.data');
        Route::get('/halaman-pemilik', [UserController::class, 'indexPemilik'])->name('indexPemilik');
        Route::post('/tambah-pemilik', [UserController::class, 'storePemilik'])->name('tambahPemilik');
        Route::resource('/pemilik', UserController::class);

        Route::get('/setting', [SettingController::class, 'index'])->name('setting.index');
        Route::get('/setting/first', [SettingController::class, 'show'])->name('setting.show');
        Route::post('/setting', [SettingController::class, 'update'])->name('setting.update');
        
    });

    Route::group(['middleware' => 'level:1,2'], function () {
        Route::get('/transaksi/baru', [PenjualanController::class, 'create'])->name('transaksi.baru');
        Route::post('/transaksi/simpan', [PenjualanController::class, 'store'])->name('transaksi.simpan');
        Route::get('/transaksi/selesai', [PenjualanController::class, 'selesai'])->name('transaksi.selesai');
        Route::get('/transaksi/nota-kecil', [PenjualanController::class, 'notaKecil'])->name('transaksi.nota_kecil');
        Route::get('/transaksi/nota-besar', [PenjualanController::class, 'notaBesar'])->name('transaksi.nota_besar');

        Route::get('/transaksi/{id}/data', [PenjualanDetailController::class, 'data'])->name('transaksi.data');
        Route::get('/transaksi/loadform/{diskon}/{total}/{diterima}', [PenjualanDetailController::class, 'loadForm'])->name('transaksi.load_form');
        Route::resource('/transaksi', PenjualanDetailController::class)
            ->except('create', 'show', 'edit');
            
        Route::get('/penjualan/data', [PenjualanController::class, 'data'])->name('penjualan.data');
        Route::get('/penjualan', [PenjualanController::class, 'index'])->name('penjualan.index');
        Route::get('/penjualan/{id}', [PenjualanController::class, 'show'])->name('penjualan.show');
        Route::delete('/penjualan/{id}', [PenjualanController::class, 'destroy'])->name('penjualan.destroy');
    });

    Route::group(['middleware' => 'level:1,3'], function () {
        Route::get('/pembelian/data', [PembelianController::class, 'data'])->name('pembelian.data');
        Route::get('/pembelian/{id}/create', [PembelianController::class, 'create'])->name('pembelian.create');
        Route::resource('/pembelian', PembelianController::class)
            ->except('create');
        Route::get('/pembelian_detail/{id}/data', [PembelianDetailController::class, 'data'])->name('pembelian_detail.data');
        Route::get('/pembelian_detail/loadform/{diskon}/{total}', [PembelianDetailController::class, 'loadForm'])->name('pembelian_detail.load_form');
        Route::resource('/pembelian_detail', PembelianDetailController::class)
            ->except('create', 'show', 'edit');

        Route::get('/produk/data', [ProdukController::class, 'data'])->name('produk.data');
        Route::post('/produk/delete-selected', [ProdukController::class, 'deleteSelected'])->name('produk.delete_selected');
        Route::post('/produk/cetak-barcode', [ProdukController::class, 'cetakBarcode'])->name('produk.cetak_barcode');
        Route::resource('/produk', ProdukController::class);
    });

    Route::group(['middleware' => 'level:1,4'], function () {

        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/data/{awal}/{akhir}', [LaporanController::class, 'data'])->name('laporan.data');
        Route::get('/laporan/pdf/{awal}/{akhir}', [LaporanController::class, 'exportPDF'])->name('laporan.export_pdf');
    });


    Route::group(['middleware' => 'level:1,3,4'], function () {
        Route::get('/permintaan_pembelian/data', [PermintaanPembelianController::class, 'data'])->name('permintaan_pembelian.data');
        Route::get('/permintaan_pembelian/{id}/terima', [PermintaanPembelianController::class, 'terima'])->name('permintaan_pembelian.terima');
        Route::get('/permintaan_pembelian/{id}/create', [PermintaanPembelianController::class, 'create'])->name('permintaan_pembelian.create');
        Route::resource('/permintaan_pembelian', PermintaanPembelianController::class)
            ->except('create');
        Route::get('/permintaan_pembelian_detail/{id}/data', [PermintaanPembelianDetailController::class, 'data'])->name('permintaan_pembelian_detail.data');
        Route::get('/permintaan_pembelian_detail/loadform/{diskon}/{total}', [PermintaanPembelianDetailController::class, 'loadForm'])->name('permintaan_pembelian_detail.load_form');
        Route::resource('/permintaan_pembelian_detail', PermintaanPembelianDetailController::class)
            ->except('create', 'show', 'edit');
    });
 
    Route::group(['middleware' => 'level:1,2,3,4'], function () {
        Route::get('/profil', [UserController::class, 'profil'])->name('user.profil');
        Route::post('/profil', [UserController::class, 'updateProfil'])->name('user.update_profil');
    });
    
});