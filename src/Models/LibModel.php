<?php

namespace Codificar\PushNotification\Models;

use Illuminate\Database\Eloquent\Relations\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Carbon\Carbon;
use DB;
use Eloquent;

class LibModel extends Eloquent
{

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

	public static function modelTest() {
		return true;
	}

}
