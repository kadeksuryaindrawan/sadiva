<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PermintaanPembelian;
use App\Models\PermintaanPembelianDetail;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\Produk;
use App\Models\Supplier;

class PermintaanPembelianController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $supplier = Supplier::orderBy('nama')->get();

        return view('permintaan_pembelian.index', compact('supplier'));
    }

    public function data()
    {
        if(auth()->user()->level == 1){
            $pembelian = PermintaanPembelian::orderBy('id_permintaan_pembelian', 'desc')->get();
        }
        if(auth()->user()->level == 3){
            $id_user = auth()->user()->id;
            $pembelian = PermintaanPembelian::where('id_user',$id_user)->orderBy('id_permintaan_pembelian', 'desc')->get();
        }
        if(auth()->user()->level == 4){
            $pembelian = PermintaanPembelian::orderBy('id_permintaan_pembelian', 'desc')->get();
        }

        return datatables()
            ->of($pembelian)
            ->addIndexColumn()
            ->addColumn('total_item', function ($pembelian) {
                return format_uang($pembelian->total_item);
            })
            ->addColumn('total_harga', function ($pembelian) {
                return 'Rp. '. format_uang($pembelian->total_harga);
            })
            ->addColumn('bayar', function ($pembelian) {
                return 'Rp. '. format_uang($pembelian->bayar);
            })
            ->addColumn('tanggal', function ($pembelian) {
                return tanggal_indonesia($pembelian->created_at, false);
            })
            ->addColumn('supplier', function ($pembelian) {
                return $pembelian->supplier->nama;
            })
            ->editColumn('diskon', function ($pembelian) {
                return $pembelian->diskon . '%';
            })
            ->addColumn('aksi', function ($pembelian) {
                if(auth()->user()->level == 3){
                    return '
                    <div class="btn-group">
                        <button onclick="showDetail(`'. route('permintaan_pembelian.show', $pembelian->id_permintaan_pembelian) .'`)" class="btn btn-xs btn-info btn-flat"><i class="fa fa-eye"></i></button>
                        <button onclick="deleteData(`'. route('permintaan_pembelian.destroy', $pembelian->id_permintaan_pembelian) .'`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                    </div>
                    ';
                }
                if(auth()->user()->level == 4 && $pembelian->status == 'belum diterima'){
                    return '
                    <div class="btn-group">
                        <button onclick="terimaData(`'. route('permintaan_pembelian.terima', $pembelian->id_permintaan_pembelian) .'`)" class="btn btn-xs btn-success btn-flat"><i class="fa fa-check"></i></button>
                        <button onclick="showDetail(`'. route('permintaan_pembelian.show', $pembelian->id_permintaan_pembelian) .'`)" class="btn btn-xs btn-info btn-flat"><i class="fa fa-eye"></i></button>
                        <button onclick="deleteData(`'. route('permintaan_pembelian.destroy', $pembelian->id_permintaan_pembelian) .'`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                    </div>
                    ';
                }

                if(auth()->user()->level == 4 && $pembelian->status != 'belum diterima'){
                    return '
                    <div class="btn-group">
                        <button onclick="showDetail(`'. route('permintaan_pembelian.show', $pembelian->id_permintaan_pembelian) .'`)" class="btn btn-xs btn-info btn-flat"><i class="fa fa-eye"></i></button>
                        <button onclick="deleteData(`'. route('permintaan_pembelian.destroy', $pembelian->id_permintaan_pembelian) .'`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                    </div>
                    ';
                }

                if(auth()->user()->level == 1 && $pembelian->status == 'belum diterima'){
                    return '
                    <div class="btn-group">
                        <button onclick="terimaData(`'. route('permintaan_pembelian.terima', $pembelian->id_permintaan_pembelian) .'`)" class="btn btn-xs btn-success btn-flat"><i class="fa fa-check"></i></button>
                        <button onclick="showDetail(`'. route('permintaan_pembelian.show', $pembelian->id_permintaan_pembelian) .'`)" class="btn btn-xs btn-info btn-flat"><i class="fa fa-eye"></i></button>
                        <button onclick="deleteData(`'. route('permintaan_pembelian.destroy', $pembelian->id_permintaan_pembelian) .'`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                    </div>
                    ';
                }

                if(auth()->user()->level == 1 && $pembelian->status != 'belum diterima'){
                    return '
                    <div class="btn-group">
                        <button onclick="showDetail(`'. route('permintaan_pembelian.show', $pembelian->id_permintaan_pembelian) .'`)" class="btn btn-xs btn-info btn-flat"><i class="fa fa-eye"></i></button>
                        <button onclick="deleteData(`'. route('permintaan_pembelian.destroy', $pembelian->id_permintaan_pembelian) .'`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                    </div>
                    ';
                }
                
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function create($id)
    {
        $id_user = auth()->user()->id;
        $pembelian = new PermintaanPembelian();
        $pembelian->id_user = $id_user;
        $pembelian->id_supplier = $id;
        $pembelian->total_item  = 0;
        $pembelian->total_harga = 0;
        $pembelian->diskon      = 0;
        $pembelian->bayar       = 0;
        $pembelian->status      = 'belum diterima';
        $pembelian->save();

        session(['id_permintaan_pembelian' => $pembelian->id_permintaan_pembelian]);
        session(['id_supplier' => $pembelian->id_supplier]);

        return redirect()->route('permintaan_pembelian_detail.index');
    }

    public function store(Request $request)
    {
        $pembelian = PermintaanPembelian::findOrFail($request->id_permintaan_pembelian);
        $pembelian->total_item = $request->total_item;
        $pembelian->total_harga = $request->total;
        $pembelian->diskon = $request->diskon;
        $pembelian->bayar = $request->bayar;
        $pembelian->update();

        $detail = PermintaanPembelianDetail::where('id_permintaan_pembelian', $pembelian->id_permintaan_pembelian)->get();
        foreach ($detail as $item) {
            $produk = Produk::find($item->id_produk);
            //$produk->stok += $item->jumlah;
            $produk->update();
        }

        return redirect()->route('permintaan_pembelian.index');
    }

    public function show($id)
    {
        $detail = PermintaanPembelianDetail::with('produk')->where('id_permintaan_pembelian', $id)->get();

        return datatables()
            ->of($detail)
            ->addIndexColumn()
            ->addColumn('kode_produk', function ($detail) {
                return '<span class="label label-success">'. $detail->produk->kode_produk .'</span>';
            })
            ->addColumn('nama_produk', function ($detail) {
                return $detail->produk->nama_produk;
            })
            ->addColumn('harga_beli', function ($detail) {
                return 'Rp. '. format_uang($detail->harga_beli);
            })
            ->addColumn('jumlah', function ($detail) {
                return format_uang($detail->jumlah);
            })
            ->addColumn('subtotal', function ($detail) {
                return 'Rp. '. format_uang($detail->subtotal);
            })
            ->rawColumns(['kode_produk'])
            ->make(true);
    }

    public function destroy($id)
    {
        $pembelian = PermintaanPembelian::find($id);
        $detail    = PermintaanPembelianDetail::where('id_permintaan_pembelian', $pembelian->id_permintaan_pembelian)->get();
        foreach ($detail as $item) {
            $produk = Produk::find($item->id_produk);
            if ($produk) {
                //$produk->stok -= $item->jumlah;
                $produk->update();
            }
            $item->delete();
        }

        $pembelian->delete();

        return response(null, 204);
    }

    public function terima($id){
        $pembelian = PermintaanPembelian::find($id);
        $detail = PermintaanPembelianDetail::where('id_permintaan_pembelian', $pembelian->id_permintaan_pembelian)->get();
        Pembelian::create([
            'id_supplier'=>$pembelian->id_supplier,
            'total_item'=>$pembelian->total_item,
            'total_harga'=>$pembelian->total_harga,
            'diskon'=>$pembelian->diskon,
            'bayar'=>$pembelian->bayar,
        ]);
        $p = Pembelian::orderBy('id_pembelian','desc')->first();
        $id_pembelian = $p->id_pembelian;
        foreach ($detail as $item) {

            PembelianDetail::create([
                'id_pembelian'=>$id_pembelian,
                'id_produk'=>$item->id_produk,
                'harga_beli'=>$item->harga_beli,
                'jumlah'=>$item->jumlah,
                'subtotal'=>$item->subtotal,
            ]);
        }
        $pembelian->status = 'diterima';
        $pembelian->update();

        return response()->json('Data berhasil disimpan', 200);
    }
}
