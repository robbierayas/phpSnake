<?php

use raylib\{
    Collision,
    Color,
    Draw,
    Input\Key,
    Rectangle,
    Text,
    Timming,
    Window,
};

final class GameLoop
{

    const CELL_SIZE = 10;
    public bool $shouldStop = false;
    private int $width;
    private int $height;
    private GameState $state;
    private float $lastStep;
    private int $difficulty = 4;
    private array $walls;

    public function __construct(
        int $width,
        int $height
    )
    {
        $this->width = $width;
        $this->height = $height;
        $this->lastStep = microtime(true);
        $s = self::CELL_SIZE;
        $this->state = new GameState(
            (int)($this->width / $s),
            (int)($this->height / $s)
        );
        $this->walls = array(
            new Rectangle(
                0,
                0,
                1,
                $this->state->maxY,
            ),
            new Rectangle(
                0,
                0,
                $this->state->maxX,
                1,
            ),
            new Rectangle(
                $this->state->maxX,
                0,
                1,
                $this->state->maxY,
            ),
            new Rectangle(
                0,
                $this->state->maxY,
                $this->state->maxX,
                1,
            ),
        );
    }

    public function start(): void
    {
        Window::init(
            $this->width,
            $this->height,
            'PHP Snake'
        );
        while (
        !Window::shouldClose()
        ) {
            Timming::setTargetFPS(60);
            while (
            !$this->shouldStop
            ) {
                $this->update();
                $this->draw();
            }
            while ($this->shouldStop && !Window::shouldClose()) {
                $this->gameOver();
                if (!Window::shouldClose() &&
                    (Key::isPressed(Key::ENTER) ||
                        Key::isPressed(Key::SPACE))) {
                    $this->restart();
                }
            }
        }
    }

    private function restart(): void
    {
        $this->shouldStop = false;
        $this->state->setInitialState();
    }

    private function update(): void
    {
        $this->shouldStop = $this->checkWallCollision($this->state->snake);
        if (!$this->shouldStop) {
            $this->shouldStop = $this->checkSnakeCollision($this->state->snake);
        }

        //snake bites fruit
        if ($this->checkFruitCollision($this->state->snake, $this->state->fruit)
        ) {
            $this->state->score();
        }

        //controls step speed
        $now = microtime(true);
        if (!$this->shouldStop &&
            $now - $this->lastStep
            > (1 / ($this->difficulty * ($this->state->score + 1)))
        ) {
            $this->state->step();
            $this->lastStep = $now;
        }

        //update direction if necessary
        if ((Key::isPressed(Key::W) || Key::isPressed(Key::UP))
            && $this->state->direction != GameState::DIRECTION_DOWN) {
            $this->state->direction = GameState::DIRECTION_UP;
        } else if ((Key::isPressed(Key::D) || Key::isPressed(Key::RIGHT))
            && $this->state->direction != GameState::DIRECTION_LEFT) {
            $this->state->direction = GameState::DIRECTION_RIGHT;
        } else if ((Key::isPressed(Key::S) || Key::isPressed(Key::DOWN))
            && $this->state->direction != GameState::DIRECTION_UP) {
            $this->state->direction = GameState::DIRECTION_DOWN;
        } else if ((Key::isPressed(Key::A) || Key::isPressed(Key::LEFT))
            && $this->state->direction != GameState::DIRECTION_RIGHT) {
            $this->state->direction = GameState::DIRECTION_LEFT;
        } else if (Key::isPressed(Key::ESCAPE)) {
            $this->shouldStop = true;
        }
    }

    private function draw(): void
    {
        Draw::begin();

        //Clear screen
        Draw::clearBackground(
            new Color(255, 255, 255, 255)
        );

        //Draw fruit
        $x = $this->state->fruit['x'];
        $y = $this->state->fruit['y'];
        Draw::rectangle(
            $x * self::CELL_SIZE,
            $y * self::CELL_SIZE,
            self::CELL_SIZE * 1.5,
            self::CELL_SIZE * 1.5,
            new Color(200, 110, 0, 255)
        );

        //Draw snake's body
        foreach (
            $this->state->snake as $coords
        ) {
            $x = $coords['x'];
            $y = $coords['y'];
            Draw::rectangle(
                $x * self::CELL_SIZE,
                $y * self::CELL_SIZE,
                self::CELL_SIZE,
                self::CELL_SIZE,
                new Color(0, 255, 0, 255)
            );
        }

        //Draw Score
        $score = "Score: {$this->state->score}";
        Text::draw(
            $score,
            $this->width - Text::measure($score, 12) - 10,
            10,
            12,
            new Color(0, 255, 0, 255)
        );

        Draw::end();
    }

    private function checkWallCollision(array $snake): bool
    {
        $head = $this->state->snake[0];
        $recSnake = new Rectangle(
            (float)$head['x'],
            (float)$head['y'],
            1,
            1,
        );
        foreach ($this->walls as $wall) {
            if (
                Collision::checkRecs(
                    $recSnake,
                    $wall
                )
            ) {
                return true;
            }
        }
        return false;
    }

    private function checkSnakeCollision(array $snake): bool
    {
        $head = $this->state->snake[0];
        if (count($snake) > 1) {
            //remove head from snake
            unset($snake[key($snake)]);
            //check if head collided with any piece
            foreach ($snake as $piece) {
                if ((int)$head['x'] == (int)$piece['x'] && (int)$head['y'] == (int)$piece['y']) {
                    return true;
                }
            }
        }
        return false;
    }

    private function checkFruitCollision(array $snake, array $fruit): bool
    {
        $head = $snake[0];
        $recSnake = new Rectangle(
            (float)$head['x'],
            (float)$head['y'],
            1,
            1,
        );

        $recFruit = new Rectangle(
            (float)$fruit['x'],
            (float)$fruit['y'],
            2,
            2,
        );
        return Collision::checkRecs(
            $recSnake,
            $recFruit
        );
    }

    private function gameOver()
    {
        Draw::begin();
        $gameOver = "GAME OVER";
        Text::draw(
            $gameOver,
            $this->width / 2 - Text::measure($gameOver, 32) / 2,
            $this->height / 2 - 15,
            32,
            new Color(0, 255, 0, 255)
        );
        $score = "Score: {$this->state->score}";
        Text::draw(
            $score,
            $this->width / 2 - Text::measure($score, 32) / 2,
            $this->height / 2 + 15,
            32,
            new Color(0, 255, 0, 255)
        );
        Draw::end();
    }
}