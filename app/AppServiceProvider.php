<?php

declare(strict_types=1);

/*
 * This file is part of York CS Negasaurus.
 *
 * (c) Graham Campbell
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace YorkCS\Negasaurus;

use Illuminate\Contracts\Container\Container;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpKernel\Exception\HttpException;
use YorkCS\Negasaurus\Exceptions\GameNotFoundException;
use YorkCS\Negasaurus\Exceptions\InvalidMoveException;
use YorkCS\Negasaurus\Exceptions\OpponentMovingException;
use YorkCS\Negasaurus\Generators\BishopGenerator;
use YorkCS\Negasaurus\Generators\GeneratorFactory;
use YorkCS\Negasaurus\Generators\GeneratorInterface;
use YorkCS\Negasaurus\Generators\KingGenerator;
use YorkCS\Negasaurus\Generators\KnightGenerator;
use YorkCS\Negasaurus\Generators\PawnGenerator;
use YorkCS\Negasaurus\Generators\QueenGenerator;
use YorkCS\Negasaurus\Generators\RookGenerator;
use YorkCS\Negasaurus\Validators\CaptureValidator;
use YorkCS\Negasaurus\Validators\FromValidator;
use YorkCS\Negasaurus\Validators\MoveValidator;
use YorkCS\Negasaurus\Validators\ValidatorFactory;
use YorkCS\Negasaurus\Validators\ValidatorInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(GeneratorInterface::class, function () {
            return new GeneratorFactory([
                State::KING   => new KingGenerator(),
                State::QUEEN  => new QueenGenerator(),
                State::BISHOP => new BishopGenerator(),
                State::KNIGHT => new KnightGenerator(),
                State::ROOK   => new RookGenerator(),
                State::PAWN   => new PawnGenerator(),
            ]);
        });

        $this->app->singleton(ValidatorInterface::class, function (Container $app) {
            return new ValidatorFactory([
                $app->make(FromValidator::class),
                $app->make(MoveValidator::class),
                $app->make(CaptureValidator::class),
            ]);
        });

        $this->app->get('/', function () {
            return new JsonResponse([
                'success' => ['message' => 'You have arrived!'],
            ]);
        });

        $this->app->post('game', function (Request $request) {
            return new JsonResponse([
                'success' => ['message' => 'Your game will begin shortly!'],
                'data'    => app(Engine::class)->start((bool) $request->get('new')),
            ], 202);
        });

        $this->app->post('game/{game}/move', function (string $game, Request $request) {
            if ($request->get('player') === null || $request->get('from_row') === null || $request->get('from_col') === null || $request->get('to_row') === null || $request->get('to_col') === null) {
                throw new HttpException(400, 'Not all the required parameters were provided.');
            }

            try {
                app(Engine::class)->move($game, (int) $request->get('player'), [(int) $request->get('from_row'), (int) $request->get('from_col')], [(int) $request->get('to_row'), (int) $request->get('to_col')]);
            } catch (GameNotFoundException $e) {
                throw new HttpException(404, $e->getMessage());
            } catch (OpponentMovingException $e) {
                throw new HttpException(403, $e->getMessage());
            } catch (InvalidMoveException $e) {
                throw new HttpException(400, $e->getMessage());
            }

            return new JsonResponse([
                'success' => ['message' => 'You move has been accepted!'],
            ], 202);
        });

        $this->app->post('game/{game}/forfeit', function (string $game, Request $request) {
            if ($request->get('player') === null) {
                throw new HttpException(400, 'Not all the required parameters were provided.');
            }

            try {
                app(Engine::class)->forfeit($game, (int) $request->get('player'));
            } catch (GameNotFoundException $e) {
                throw new HttpException(404, $e->getMessage());
            }

            return new JsonResponse([
                'success' => ['message' => 'You forfeit has been accepted!'],
            ], 202);
        });
    }
}
