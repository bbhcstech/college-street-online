<?php
namespace App\Http\Controllers;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
class SupportController extends Controller {
    public function index(Request $request){ $tickets=SupportTicket::where('user_id',$request->user()->id)->latest()->paginate(10); return view('pages.support',compact('tickets')); }
    public function store(Request $request){ $data=$request->validate(['subject'=>['required','string','max:150'],'message'=>['required','string','max:5000']]); $request->user()->supportTickets()->create($data); return back()->with('success','Your support request has been submitted.'); }
}
