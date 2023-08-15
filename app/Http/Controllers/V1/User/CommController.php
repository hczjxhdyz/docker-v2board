<?php

namespace App\Http\Controllers\V1\User;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Utils\Dict;
use Illuminate\Http\Request;

class CommController extends Controller
{
    public function config()
    {
        return response([
            'data' => [
                'is_telegram' => (int)Setting('telegram_bot_enable', 0),
                'telegram_discuss_link' => Setting('telegram_discuss_link'),
                'stripe_pk' => Setting('stripe_pk_live'),
                'withdraw_methods' => Setting('commission_withdraw_method', Dict::WITHDRAW_METHOD_WHITELIST_DEFAULT),
                'withdraw_close' => (int)Setting('withdraw_close_enable', 0),
                'currency' => Setting('currency', 'CNY'),
                'currency_symbol' => Setting('currency_symbol', '¥'),
                'commission_distribution_enable' => (int)Setting('commission_distribution_enable', 0),
                'commission_distribution_l1' => Setting('commission_distribution_l1'),
                'commission_distribution_l2' => Setting('commission_distribution_l2'),
                'commission_distribution_l3' => Setting('commission_distribution_l3')
            ]
        ]);
    }

    public function getStripePublicKey(Request $request)
    {
        $payment = Payment::where('id', $request->input('id'))
            ->where('payment', 'StripeCredit')
            ->first();
        if (!$payment) abort(500, 'payment is not found');
        return response([
            'data' => $payment->config['stripe_pk_live']
        ]);
    }
}
