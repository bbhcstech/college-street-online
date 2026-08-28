<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
class SupportController extends Controller {
    public function index(Request $request){ $tickets=SupportTicket::with('user')->when($request->filled('status'),fn($q)=>$q->where('status',$request->status))->latest()->paginate(15)->withQueryString(); return view('admin.support.index',compact('tickets')); }
    public function show(SupportTicket $supportTicket){ return view('admin.support.show',['ticket'=>$supportTicket->load('user')]); }
    public function update(Request $request,SupportTicket $supportTicket){ $data=$request->validate(['status'=>['required','in:open,in_progress,resolved,closed'],'admin_reply'=>['nullable','required_if:status,resolved,closed','string','max:5000']]); $data['replied_at']=filled($data['admin_reply']??null)?now():$supportTicket->replied_at; $supportTicket->update($data); return back()->with('success','Support ticket updated.'); }
}
