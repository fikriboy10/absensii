<?

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request;

Route::get('/test-user', function () {
    $user = Auth::guard('anggota')->user();
    return response()->json($user);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
