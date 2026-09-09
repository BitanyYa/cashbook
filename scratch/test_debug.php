<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::factory()->create(['name' => 'Manager User']);
$biz = App\Models\Business::factory()->create();
$book = App\Models\Book::factory()->create(['business_id' => $biz->id, 'name' => 'Test Book']);
$biz->users()->attach($user->id, ['role' => 'employee']);
$book->users()->attach($user->id, ['role' => 'primary_admin']);

echo "User business role: " . $user->getBusinessRole($biz) . PHP_EOL;
echo "User book role: " . $user->getBookRole($book) . PHP_EOL;
echo "Can view book: " . ($user->canViewBook($book) ? 'YES' : 'NO') . PHP_EOL;

$req = Illuminate\Http\Request::create('/books/' . $book->id, 'GET');
$req->setUserResolver(fn() => $user);
$req->attributes->set('activeBusiness', $biz);

$controller = new App\Http\Controllers\BookController();
try {
    $res = $controller->show($req, $book);
    echo "Response status: " . (method_exists($res, 'getStatusCode') ? $res->getStatusCode() : 'Rendered View') . PHP_EOL;
} catch (\Throwable $e) {
    echo "Exception: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
}
