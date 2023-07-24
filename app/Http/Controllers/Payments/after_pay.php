<?php
	$order = \App\Models\Orders\Order::find($transaction->transactionable_id);

	$order->update([
		'is_paid' => 1
	]);