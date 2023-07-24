<?php
	if($model->payment_method == 'click') return $model->amount == intval($amount);

	return $model->amount * 100 == intval($amount);