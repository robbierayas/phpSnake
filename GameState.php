<?php

final class GameState
{
    const DIRECTION_UP = 1;
    const DIRECTION_DOWN = 2;
    const DIRECTION_LEFT = 3;
    const DIRECTION_RIGHT = 4;
    public int $score = 0;
    public int $direction = 1;
    public array $fruit;
    public int $maxX;
    public int $maxY;
    /**
     * @var int[][]
     */
    public array $snake;

    public function __construct(
        int $maxX,
        int $maxY
    )
    {
        $this->maxX = $maxX;
        $this->maxY = $maxY;
        $this->setInitialState();
    }

    public function setInitialState(){

        $this->snake = [
            $this->craftInitialCoords(),
        ];

        $this->fruit = $this->craftRandomCoords();
        $this->score=0;
        $this->direction=self::DIRECTION_UP;
    }

    private function incrementBody(): void
    {
        $newHead = $this->snake[0];

        //Adjusts head direction
        switch ($this->direction) {
            case self::DIRECTION_UP:
                $newHead['y']--;
                break;
            case self::DIRECTION_DOWN:
                $newHead['y']++;
                break;
            case self::DIRECTION_RIGHT:
                $newHead['x']++;
                break;
            case self::DIRECTION_LEFT:
                $newHead['x']--;
                break;
        }

        $this->snake = array_merge(
            [$newHead],
            $this->snake
        );
    }

    public function score(): void
    {
        $this->score++;
        $this->incrementBody();
        $this->fruit = $this->craftRandomCoords();
    }

    public function step(): void
    {
        $this->incrementBody();

        //Remove last element
        array_pop($this->snake);

        //Warp body if necessary
//        foreach ($this->snake as &$coords){
//            if($coords['x']>$this->maxX-1){
//                $coords['x']=0;
//            }else if ($coords['x']<0){
//                $coords['x']=$this->maxX-1;
//            }
//
//            if($coords['y']>$this->maxY-1){
//                $coords['y']=0;
//            } else if ($coords['y']<0){
//                $coords['y']=$this->maxY-1;
//            }
//        }
    }

    private function craftInitialCoords(): array
    {
        return array('x' => (float)$this->maxX / 2, 'y' => (float)$this->maxY / 2);
    }

    private function craftRandomCoords(): array
    {
        return array('x' => rand(10, $this->maxX - 10), 'y' => rand(10, $this->maxY - 10));
    }
}