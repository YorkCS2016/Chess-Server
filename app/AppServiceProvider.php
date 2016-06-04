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

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpKernel\Exception\HttpException;
use YorkCS\Negasaurus\Exceptions\GameNotFoundException;
use YorkCS\Negasaurus\Exceptions\InvalidMoveException;
use YorkCS\Negasaurus\Exceptions\OpponentMovingException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
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
                app(Engine::class)->move($game, (int) $request->get('player'), [(int) $request->get('from_row'), (int) $request->get('from_rol')], [(int) $request->get('to_row'), (int) $request->get('to_col')]);
            } catch (GameNotFoundException $e) {
                throw new HttpException(404, 'The given game does not exist.');
            } catch (OpponentMovingException $e) {
                throw new HttpException(403, 'Your opponent is currently moving.');
            } catch (InvalidMoveException $e) {
                throw new HttpException(400, 'Your move was not valid.');
            }

            return new JsonResponse([
                'success' => ['message' => 'You move has been accepted!'],
            ], 202);
        });

        $this->app->post('game/{game}/forfit', function (string $game, Request $request) {
            if ($request->get('player') === null) {
                throw new HttpException(400, 'Not all the required parameters were provided.');
            }

            app(Engine::class)->forfit($game, (int) $request->get('player'));

            return new JsonResponse([
                'success' => ['message' => 'You forgit has been accepted!'],
            ], 202);
        });
    }
}
