<?php

use App\Http\Controllers\DensidadeController;
use App\Http\Controllers\TemperaturaController;
use App\Http\Controllers\CorController;
use App\Http\Controllers\PressaoController;
use App\Http\Controllers\DensimetroController;
use App\Http\Controllers\RefratometroController;
use App\Http\Controllers\PressaoTemperaturaController;
use App\Http\Controllers\AbvController;
use App\Http\Controllers\ExtracaoFrioController;
use App\Http\Controllers\PercentualAcucarController;
use App\Http\Controllers\LeveduraController;
use App\Http\Controllers\DiluicaoAlcoolicaController;
use App\Http\Controllers\AdicaoAlcoolicaController;
use App\Http\Controllers\MotorController;
use App\Http\Controllers\VolumeMostoController;
use App\Http\Controllers\PrimingController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn() => Inertia::render('Home'))->name('home');

Route::get('/densidade',  [DensidadeController::class, 'show'])->name('densidade');
Route::post('/densidade', [DensidadeController::class, 'calcular'])->name('densidade.calcular');

Route::get('/temperatura',  [TemperaturaController::class, 'show'])->name('temperatura');
Route::post('/temperatura', [TemperaturaController::class, 'calcular'])->name('temperatura.calcular');

Route::get('/cor',  [CorController::class, 'show'])->name('cor');
Route::post('/cor', [CorController::class, 'calcular'])->name('cor.calcular');

Route::get('/pressao',  [PressaoController::class, 'show'])->name('pressao');
Route::post('/pressao', [PressaoController::class, 'calcular'])->name('pressao.calcular');

Route::get('/densimetro',  [DensimetroController::class, 'show'])->name('densimetro');
Route::post('/densimetro', [DensimetroController::class, 'calcular'])->name('densimetro.calcular');

Route::get('/refratometro',  [RefratometroController::class, 'show'])->name('refratometro');
Route::post('/refratometro', [RefratometroController::class, 'calcular'])->name('refratometro.calcular');

Route::get('/pressao-temperatura',  [PressaoTemperaturaController::class, 'show'])->name('pressao-temperatura');
Route::post('/pressao-temperatura', [PressaoTemperaturaController::class, 'calcular'])->name('pressao-temperatura.calcular');

Route::get('/abv',  [AbvController::class, 'show'])->name('abv');
Route::post('/abv', [AbvController::class, 'calcular'])->name('abv.calcular');

Route::get('/extracao-frio',  [ExtracaoFrioController::class, 'show'])->name('extracao-frio');
Route::post('/extracao-frio', [ExtracaoFrioController::class, 'calcular'])->name('extracao-frio.calcular');

Route::get('/percentual-acucar',  [PercentualAcucarController::class, 'show'])->name('percentual-acucar');
Route::post('/percentual-acucar', [PercentualAcucarController::class, 'calcular'])->name('percentual-acucar.calcular');

Route::get('/levedura',  [LeveduraController::class, 'show'])->name('levedura');
Route::post('/levedura', [LeveduraController::class, 'calcular'])->name('levedura.calcular');

Route::get('/diluicao-alcoolica',  [DiluicaoAlcoolicaController::class, 'show'])->name('diluicao-alcoolica');
Route::post('/diluicao-alcoolica', [DiluicaoAlcoolicaController::class, 'calcular'])->name('diluicao-alcoolica.calcular');

Route::get('/adicao-alcoolica',  [AdicaoAlcoolicaController::class, 'show'])->name('adicao-alcoolica');
Route::post('/adicao-alcoolica', [AdicaoAlcoolicaController::class, 'calcular'])->name('adicao-alcoolica.calcular');

Route::get('/motor',  [MotorController::class, 'show'])->name('motor');
Route::post('/motor', [MotorController::class, 'calcular'])->name('motor.calcular');

Route::get('/volume-mosto',  [VolumeMostoController::class, 'show'])->name('volume-mosto');
Route::post('/volume-mosto', [VolumeMostoController::class, 'calcular'])->name('volume-mosto.calcular');

Route::get('/priming',  [PrimingController::class, 'show'])->name('priming');
Route::post('/priming', [PrimingController::class, 'calcular'])->name('priming.calcular');
