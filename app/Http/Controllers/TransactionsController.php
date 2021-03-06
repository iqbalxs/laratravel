<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Session;
use DB;
use App\Transaction;
use Yajra\Datatables\Datatables;
use Yajra\Datatables\Html\Builder;
use Auth;
use Excel;

class TransactionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     public function index(Request $request, Builder $htmlBuilder)
       {
         if ($request->ajax()) {
           $data = Transaction::with('user','place','car','admin')->get();
           return Datatables::of($data)
             ->addColumn('action', function($transaction){
               return view('datatable._action_transaction', [
                 'model' => $transaction,
                 'form_url' => route('transactions.destroy', $transaction->id),
                 'edit_url' => route('transactions.edit', $transaction->id),
                 'conf_url' => route('transactions.show', $transaction->id),
                 'confirm_message' => 'Yakin mau menghapus transaksi ' . $transaction->code . '?'
               ]);
             })->make(true);
           }

           $html = $htmlBuilder
             ->addColumn(['data' => 'code', 'name'=>'code', 'title'=>'Kode Transaksi'])
             ->addColumn(['data' => 'user.name', 'name'=>'user.name', 'title'=>'Nama Pelanggan'])
             ->addColumn(['data' => 'place.name', 'name'=>'place.name', 'title'=>'Tujuan'])
             ->addColumn(['data' => 'car.name', 'name'=>'car.name', 'title'=>'Merk mobil'])
             ->addColumn(['data' => 'total_participants', 'name'=>'total_participants', 'title'=>'Jumlah orang'])
             ->addColumn(['data' => 'start_date', 'name'=>'start_date', 'title'=>'Tanggal Pergi'])
             ->addColumn(['data' => 'end_date', 'name'=>'end_date', 'title'=>'Tanggal Kembali'])
             ->addColumn(['data' => 'total_cost', 'name'=>'total_cost', 'title'=>'Total Harga'])
             ->addColumn(['data' => 'admin.name', 'name'=>'admin.name', 'title'=>'Admin'])
             ->addColumn(['data' => 'status', 'name'=>'status', 'title'=>'Status'])
             ->addColumn(['data' => 'action', 'name'=>'action', 'title'=>'', 'orderable'=>false, 'searchable'=>false]);

             return view('transactions.index')->with(compact('html'));
       }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $transaction = Transaction::with('user','place','car')->find($id);
        $confirmation = DB::table('confirmations')
        ->where('transaction_id','=', $id)->get();
        return view('transactions.confirmation')->with(compact('transaction','confirmation'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
      $transaction = Transaction::find($id);
      return view('transactions.edit')->with(compact('transaction'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
      $this->validate($request, [
        'user_id' => 'required',
        'place_id' => 'required',
        'car_id' => 'required',
        'total_participants' => 'required|numeric',
        'total_cost' => 'required|numeric',
        'status' => 'required'
      ]);

      $transaction = Transaction::find($id);
      $transaction->update($request->all());

      Session::flash("flash_notification", [
        "level"=>"success",
        "message"=>"Transaksi Berhasil Diubah"
      ]);

    return redirect()->route('transactions.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      $transaction = Transaction::find($id);

      // hapus image lama, jika ada
      if ($transaction->confirmation->image) {
        $old_image = $transaction->confirmation->image;
        $filepath = public_path() . DIRECTORY_SEPARATOR . 'img'
        . DIRECTORY_SEPARATOR . $transaction->confirmation->image;
        try {
          File::delete($filepath);
        } catch (FileNotFoundException $e) {
          // File sudah dihapus/tidak ada
        }
      }
      $transaction->delete();

      Session::flash("flash_notification", [
        "level"=>"success",
        "message"=>"Data Berhasil dihapus"
      ]);

      return redirect()->route('transactions.index');
    }

    public function export()
    {
      return view('transactions.export');
    }

    public function exportPost(Request $request)
    {
      // validasi
      $this->validate($request, [
        'month'=>'required',
      ], [
        'month.required'=>'Anda belum memilih bulan. Pilih bulan terlebih dahulu!'
      ]);

      $transactions = Transaction::with('user','place','car','admin')
                      ->whereMonth('created_at', $request->get('month'))
                      ->orderBy('status', 'asc')
                      ->get();

      Excel::create('Laporan Transaksi Bulanan bdgtranservice', function($excel) use ($transactions) {

        // Set property
        $excel->setTitle('Laporan Transaksi Bulanan bdgtranservice')
        ->setCreator(Auth::user()->name);
        $excel->sheet('Laporan Transaksi', function($sheet) use ($transactions) {
          $row = 1;
          $sheet->row($row, [
            'Kode Transaksi',
            'Nama Pelanggan',
            'Tujuan',
            'Merk Mobil',
            'Jumlah Orang',
            'Tangga Pergi',
            'Tanggal Kembali',
            'Total Harga',
            'Admin',
            'Status'
          ]);
          foreach ($transactions as $transaction) {
            $sheet->row(++$row, [
              $transaction->code,
              $transaction->user->name,
              $transaction->place->name,
              $transaction->car->name,
              $transaction->total_participants,
              $transaction->start_date,
              $transaction->end_date,
              $transaction->total_cost,
              $transaction->admin->name,
              $transaction->status
            ]);
          }
        });
      })->export('xls');
    }
}
