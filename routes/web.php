<?php

use App\Http\Controllers\ConversaoController;
use App\Http\Controllers\CorrecaoController;
use App\Http\Controllers\CalculoController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn() => Inertia::render('Home'))->name('home');

// Módulo 1 — Conversões
Route::prefix('conversao')->name('conversao.')->group(function () {
    Route::get('densidade',   [ConversaoController::class, 'densidade'])->name('densidade');
    Route::post('densidade',  [ConversaoController::class, 'calcularDensidade'])->name('densidade.calcular');

    Route::get('temperatura',  [ConversaoController::class, 'temperatura'])->name('temperatura');
    Route::post('temperatura', [ConversaoController::class, 'calcularTemperatura'])->name('temperatura.calcular');

    Route::get('cor',  [ConversaoController::class, 'cor'])->name('cor');
    Route::post('cor', [ConversaoController::class, 'calcularCor'])->name('cor.calcular');

    Route::get('pressao',  [ConversaoController::class, 'pressao'])->name('pressao');
    Route::post('pressao', [ConversaoController::class, 'calcularPressao'])->name('pressao.calcular');
});

// Módulo 2 — Correções
Route::prefix('correcao')->name('correcao.')->group(function () {
    Route::get('densimetro',  [CorrecaoController::class, 'densimetro'])->name('densimetro');
    Route::post('densimetro', [CorrecaoController::class, 'calcularDensimetro'])->name('densimetro.calcular');

    Route::get('refratometro',  [CorrecaoController::class, 'refratometro'])->name('refratometro');
    Route::post('refratometro', [CorrecaoController::class, 'calcularRefratometro'])->name('refratometro.calcular');

    Route::get('pressao-temperatura',  [CorrecaoController::class, 'pressaoTemperatura'])->name('pressao-temperatura');
    Route::post('pressao-temperatura', [CorrecaoController::class, 'calcularPressaoTemperatura'])->name('pressao-temperatura.calcular');
});

// Módulo 3 — Cálculos Simples
Route::prefix('calculo')->name('calculo.')->group(function () {
    Route::get('abv',  [CalculoController::class, 'abv'])->name('abv');
    Route::post('abv', [CalculoController::class, 'calcularAbv'])->name('abv.calcular');

    Route::get('extracao-frio',  [CalculoController::class, 'extracaoFrio'])->name('extracao-frio');
    Route::post('extracao-frio', [CalculoController::class, 'calcularExtracaoFrio'])->name('extracao-frio.calcular');

    Route::get('percentual-acucar',  [CalculoController::class, 'percentualAcucar'])->name('percentual-acucar');
    Route::post('percentual-acucar', [CalculoController::class, 'calcularPercentualAcucar'])->name('percentual-acucar.calcular');

    Route::get('levedura',  [CalculoController::class, 'levedura'])->name('levedura');
    Route::post('levedura', [CalculoController::class, 'calcularLevedura'])->name('levedura.calcular');

    Route::get('diluicao-alcoolica',  [CalculoController::class, 'diluicaoAlcoolica'])->name('diluicao-alcoolica');
    Route::post('diluicao-alcoolica', [CalculoController::class, 'calcularDiluicaoAlcoolica'])->name('diluicao-alcoolica.calcular');

    Route::get('adicao-alcoolica',  [CalculoController::class, 'adicaoAlcoolica'])->name('adicao-alcoolica');
    Route::post('adicao-alcoolica', [CalculoController::class, 'calcularAdicaoAlcoolica'])->name('adicao-alcoolica.calcular');

    Route::get('motor',  [CalculoController::class, 'motor'])->name('motor');
    Route::post('motor', [CalculoController::class, 'calcularMotor'])->name('motor.calcular');

    Route::get('volume-mosto',  [CalculoController::class, 'volumeMosto'])->name('volume-mosto');
    Route::post('volume-mosto', [CalculoController::class, 'calcularVolumeMosto'])->name('volume-mosto.calcular');

    Route::get('priming',  [CalculoController::class, 'priming'])->name('priming');
    Route::post('priming', [CalculoController::class, 'calcularPriming'])->name('priming.calcular');
});

// Módulos 4–7 (a implementar)
