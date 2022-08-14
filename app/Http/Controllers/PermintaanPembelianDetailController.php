<?php

namespace App\Http\Controllers;

use App\Models\PermintaanPembelian;
use App\Models\PermintaanPembelianDetail;
use App\Models\Produk;
use App\Models\Supplier;
use Illuminate\Http\Request;

class PermintaanPembelianDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $id_permintaan_pembelian = session('id_permintaan_pembelian');
        $produk = Produk::orderBy('nama_produk')->get();
        $supplier = Supplier::find(session('id_supplier'));
        $diskon = PermintaanPembelian::find($id_permintaan_pembelian)->diskon ?? 0;

        if (! $supplier) {
            abort(404);
        }

        return view('permintaan_pembelian_detail.index', compact('id_permintaan_pembelian', 'produk', 'supplier', 'diskon'));
    }

    public function data($id)
    {
        $detail = PermintaanPembelianDetail::with('produk')
            ->where('id_permintaan_pembelian', $id)
            ->get();
        $data = array();
        $total = 0;
        $total_item = 0;

        foreach ($detail as $item) {
            $row = array();
            $row['kode_produk'] = '<span class="label label-success">'. $item->produk['kode_produk'] .'</span';
            $row['nama_produk'] = $item->produk['nama_produk'];
            $row['harga_beli']  = '<input type="number" style="width:100%" class="form-control input-md harga_beli" data-id="'. $item->id_permintaan_pembelian_detail .'" value="'. $item->harga_beli .'">';
            $row['jumlah']      = '<input type="number" class="form-control input-sm quantity" data-id="'. $item->id_permintaan_pembelian_detail .'" value="'. $item->jumlah .'">';
            $row['subtotal']    = 'Rp. '. format_uang($item->subtotal);
            $row['aksi']        = '<div class="btn-group">
                                    <button onclick="deleteData(`'. route('permintaan_pembelian_detail.destroy', $item->id_permintaan_pembelian_detail) .'`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                                </div>';
            $data[] = $row;

            $total += $item->harga_beli * $item->jumlah;
            $total_item += $item->jumlah;
        }
        $data[] = [
            'kode_produk' => '
                <div class="total hide">'. $total .'</div>
                <div class="total_item hide">'. $total_item .'</div>',
            'nama_produk' => '',
            'harga_beli'  => '',
            'jumlah'      => '',
            'subtotal'    => '',
            'aksi'        => '',
        ];

        return datatables()
            ->of($data)
            ->addIndexColumn()
            ->rawColumns(['aksi','harga_beli', 'kode_produk', 'jumlah'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $produk = Produk::where('id_produk', $request->id_produk)->first();
        if (! $produk) {
            return response()->json('Data gagal disimpan', 400);
        }

        $detail = new PermintaanPembelianDetail();
        $detail->id_permintaan_pembelian = $request->id_permintaan_pembelian;
        $detail->id_produk = $produk->id_produk;
        $detail->harga_beli = 0;
        $detail->jumlah = 1;
        $detail->subtotal = 0;
        $detail->save();

        return response()->json('Data berhasil disimpan', 200);
    }

    public function update(Request $request, $id)
    {
        if($request->harga_beli){
            $detail = PermintaanPembelianDetail::find($id);
            $detail->harga_beli = $request->harga_beli;
            $detail->subtotal = $request->harga_beli * $detail->jumlah;
            $detail->update();
        }
        if($request->jumlah){
            $detail = PermintaanPembelianDetail::find($id);
            $detail->jumlah = $request->jumlah;
            $detail->subtotal = $detail->harga_beli * $request->jumlah;
            $detail->update();
        }
    }

    public function destroy($id)
    {
        $detail = PermintaanPembelianDetail::find($id);
        $detail->delete();

        return response(null, 204);
    }

    public function loadForm($diskon, $total)
    {
        $bayar = $total - ($diskon / 100 * $total);
        $data  = [
            'totalrp' => format_uang($total),
            'bayar' => $bayar,
            'bayarrp' => format_uang($bayar),
            'terbilang' => ucwords(terbilang($bayar). ' Rupiah')
        ];

        return response()->json($data);
    }
}
