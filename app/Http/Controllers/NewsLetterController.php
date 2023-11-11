<?php

namespace App\Http\Controllers;

use App\Models\NewsLetter;
use App\Models\User;
use Illuminate\Http\Request;

class NewsLetterController extends Controller
{
    public function send(Request $request)
    {
        $request->validate([
            'text' => 'required',
            'all' => 'required|boolean'
        ]);

        if ($request->input('all') == 1) {
            $clients = User::all();
        } else {
            $clients = User::where('subscriber', 1)
                ->get();
        }

        $sent = 0;
        $notSent = 0;
        foreach ($clients as $client) {
            $res = $this->send_sms($client->login, $request->input('text'));

            $res ? $sent ++ : $notSent ++;
        }

        $data = [
            'text' => $request->input('text'),
            'all' => $request->input('all'),
            'sent' => $sent,
            'not_sent' => $notSent,
            'admin_id' => auth()->id()
        ];
        NewsLetter::create($data);

        return response([
            'message' => 'Successfully sent'
        ]);
    }

    public function index()
    {
        $newsletters = NewsLetter::with('admin')
            ->latest()
            ->paginate(12);

        return response([
            'newsletters' => $newsletters
        ]);
    }

    public function destroy(int $id)
    {
        $newsLetter = NewsLetter::find($id);
        if ($newsLetter) $newsLetter->delete();
        else return response([
            'message' => 'Newsletter nopt found'
        ], 404);

        return response([
            'message' => 'Successfully destroyed'
        ]);
    }
}
