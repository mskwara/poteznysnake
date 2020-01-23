<template>
  <div id="app">
    <p v-if="gameMode == 'coop'">Liczba punktów: {{snake1.points+snake2.points}}</p>
    <p v-if="gameMode == 'battle'">Gracz 1: {{snake1.points}} punktów, Gracz 2: {{snake2.points}} punktów</p>
    <div id="map" v-bind:style="setMap()">
      <div :class="snakeClass(part.id)" :key="snake1.snakeId+part.id" v-for="part in snake1.parts" v-bind:style="partPosition(part)"></div>
      <div :class="snakeClass(part.id)" :key="snake2.snakeId+part.id" v-for="part in snake2.parts" v-bind:style="partPosition(part)"></div>
      <div class="apple" v-bind:style="applePosition()"></div>
    </div>

    <md-button class="md-raised md-primary mode" @click="changeMode()">Teraz grane: {{gameMode}}</md-button>
    <md-dialog-confirm
      :md-active.sync="gameOverAlert"
      md-title="GAME OVER"
      :md-content="alertText"
      md-confirm-text="Jeszcze raz"
      @md-confirm="reset" />
  </div>
</template>

<script>

export default {
  data(){
    return {
      SIZE: 40,
      snake1: {
          snakeId: "0",
          parts: [
            {id: 0, x: 4, y: 6},
            {id: 1, x: 3, y: 6},
            {id: 2, x: 2, y: 6},
            {id: 3, x: 1, y: 6},
            {id: 4, x: 0, y: 6},
          ],
          nextId: 5,
          direction: "right",
          snakeLength: 5,
          alreadyTurned: false,
          nextMoves: [],
          isAppleEaten: false,
          points: 0,
      },
      snake2: {
          snakeId: "1",
          parts: [
            {id: 0, x: 4, y: 2},
            {id: 1, x: 3, y: 2},
            {id: 2, x: 2, y: 2},
            {id: 3, x: 1, y: 2},
            {id: 4, x: 0, y: 2},
          ],
          nextId: 5,
          direction: "right",
          snakeLength: 5,
          alreadyTurned: false,
          nextMoves: [],
          isAppleEaten: false,
          points: 0,
      },
      gameMode: "coop",
      alive: true,
      speed: 200,
      mapSize: 10,
      apple: {
        x: 8,
        y: 8
      },

      pointsFactor: 1,
      applesCount: 0,
      whyDied: "",
      gameOverAlert: false,
      alertText: "Koniec gry"
    }
  },
  mounted: function(){
    this.putApple();
    this.update();
    window.addEventListener("keyup", e => {
      if(e.keyCode == 37){  //left
        this.turn("left", this.snake1);
      }
      if(e.keyCode == 38){  //up
        this.turn("up", this.snake1);
      }
      if(e.keyCode == 39){  //right
        this.turn("right", this.snake1);
      }
      if(e.keyCode == 40){  //down
        this.turn("down", this.snake1);
      }
      //snake2
      if(e.keyCode == 65){  //left
        this.turn("left", this.snake2);
      }
      if(e.keyCode == 87){  //up
        this.turn("up", this.snake2);
      }
      if(e.keyCode == 68){  //right
        this.turn("right", this.snake2);
      }
      if(e.keyCode == 83){  //down
        this.turn("down", this.snake2);
      }
    });

  },
  methods: {
    changeMode(){
      if(this.gameMode == "coop"){
        this.gameMode = "battle";
      }
      else if(this.gameMode == "battle"){
        this.gameMode = "coop";
      }
    },
    partPosition(part) {
      var positionStyle = {top: this.SIZE*part.y+'px', left: this.SIZE*part.x+'px'};
      return positionStyle;
    },
    applePosition() {
      var positionStyle = {top: this.SIZE*this.apple.y+'px', left: this.SIZE*this.apple.x+'px'};
      return positionStyle;
    },
    snakeClass(id){
      if(id == 0){
        return 'snake_head';
      }
      else return 'snake_part';
    },
    setMap(){
      var mapStyle = {width: this.mapSize*40+1+'px', height: this.mapSize*40+1+'px'};
      return mapStyle;
    },
    checkSnakeAlive(snake){
      if(snake.parts[0].x == -1
         || snake.parts[0].x == this.mapSize
          || snake.parts[0].y == -1
           || snake.parts[0].y == this.mapSize){ //za mapą
        this.alive = false;
        this.whyDied = snake.snakeId+" za mapą";
      }
      else {
        for(var i = snake.snakeLength-1 ; i > 0 ; i--){      //czy uderza w ogon
          if(Object(snake.parts[i]).y == snake.parts[0].y && Object(snake.parts[i]).x == snake.parts[0].x){
            this.alive = false;
            this.whyDied = snake.snakeId+" sie sam zjadł";
            break;
          }
        }
      }

    },
    checkSnakeEatSnake(){
      if(this.snake1.parts[0].x == this.snake2.parts[0].x && this.snake1.parts[0].y == this.snake2.parts[0].y){
        this.alive = false;
        this.whyDied = "Czołówka!!";
        return;
      }
      for(var k = 0 ; k < this.snake2.snakeLength ; k++){   //czy snaki sie same uderzaja
        if(this.snake1.parts[0].x == this.snake2.parts[k].x && this.snake1.parts[0].y == this.snake2.parts[k].y){
          this.alive = false;
          this.whyDied = "Gracz 1 sie wyłożył";
        }
      }
      for(var j = 0 ; j < this.snake1.snakeLength ; j++){   //czy snaki sie same uderzaja
        if(this.snake2.parts[0].x == this.snake1.parts[j].x && this.snake2.parts[0].y == this.snake1.parts[j].y){
          this.alive = false;
          this.whyDied = "Gracz 2 sie wyłożył";
        }
      }
    },
    checkAppleEaten(snake){
      if(snake.parts[0].x == this.apple.x && snake.parts[0].y == this.apple.y){
        snake.isAppleEaten = true;
        snake.points += 10*this.pointsFactor;
        this.applesCount++;
        // if(this.applesCount%5 == 0){
        //   this.pointsFactor += 1;
        //   //this.speed -= 20;
        // }
        this.putApple();
      }
    },
    update(){
      if(this.alive){
        this.move(this.snake1);
        this.move(this.snake2);
        this.checkSnakeAlive(this.snake1);
        this.checkSnakeAlive(this.snake2);
        this.checkSnakeEatSnake();

        if(!this.alive){
          this.gameOver();
        }
        this.checkAppleEaten(this.snake1);
        this.checkAppleEaten(this.snake2);

        setTimeout(this.update, this.speed);
      }
      // else {
      //   this.reset();
      // }
    },
    gameOver(){
      this.alertText = "Koniec gry, powód: "+this.whyDied;
      if(this.snake1.points > this.snake2.points){
        this.alertText+=". Wygrywa gracz 1";
      }
      if(this.snake1.points < this.snake2.points){
        this.alertText+=". Wygrywa gracz 2";
      }
      if(this.snake1.points == this.snake2.points){
        this.alertText+=". Remis";
      }
      this.gameOverAlert = true;
    },
    move(snake){
      if(snake.isAppleEaten == true){
        var newPart = {};
        newPart.id = snake.nextId;
        newPart.x = Object(snake.parts[snake.snakeLength-1]).x;
        newPart.y = Object(snake.parts[snake.snakeLength-1]).y;
        snake.parts.push(newPart);
        snake.snakeLength++;
        snake.nextId++;
        snake.isAppleEaten = false;
      }
      for(var i = snake.snakeLength-1 ; i > 0 ; i--){      //ruch całego ciała
        Object(snake.parts[i]).y = snake.parts[i-1].y;
        Object(snake.parts[i]).x = snake.parts[i-1].x;
      }
      if(snake.nextMoves.length != 0){
        if(snake.nextMoves[0] == "up"){     //ruchy głowy
          snake.parts[0].y--;
        }
        else if(snake.nextMoves[0] == "right"){
          snake.parts[0].x++;
        }
        else if(snake.nextMoves[0] == "down"){
          snake.parts[0].y++;
        }
        else if(snake.nextMoves[0] == "left"){
          snake.parts[0].x--;
        }
        snake.nextMoves.splice(0,1);
      }
      else {
        if(snake.direction == "up"){     //ruchy głowy
          snake.parts[0].y--;
        }
        else if(snake.direction == "right"){
          snake.parts[0].x++;
        }
        else if(snake.direction == "down"){
          snake.parts[0].y++;
        }
        else if(snake.direction   == "left"){
          snake.parts[0].x--;
        }
      }
    },
    turn(dir, snake){
      if(snake.direction == "up" && dir == "down") return;
      if(snake.direction == "right" && dir == "left") return;
      if(snake.direction == "down" && dir == "up") return;
      if(snake.direction == "left" && dir == "right") return;
      if(snake.nextMoves.length < 2){
        snake.direction = dir;
        snake.nextMoves.push(dir);
      }
    },
    reset(){
      this.alive = true;
      this.snake1.direction = "right";
      this.snake2.direction = "right";
      this.snake1.parts = [
        {id: 0, x: 6, y: 6},
        {id: 1, x: 5, y: 6},
        {id: 2, x: 4, y: 6},
        {id: 3, x: 3, y: 6},
        {id: 4, x: 2, y: 6},
      ];
      this.snake2.parts = [
        {id: 0, x: 6, y: 2},
        {id: 1, x: 5, y: 2},
        {id: 2, x: 4, y: 2},
        {id: 3, x: 3, y: 2},
        {id: 4, x: 2, y: 2},
      ];
      this.snake1.snakeLength = 5;
      this.snake2.snakeLength = 5;
      this.snake1.isAppleEaten = false;
      this.snake2.isAppleEaten = false;
      this.snake1.points = 0;
      this.snake2.points = 0;
      this.speed = 200;
      this.pointsFactor = 1;

      this.snake1.nextId = 5,
      this.snake2.nextId = 5,
      this.snake1.alreadyTurned = false,
      this.snake2.alreadyTurned = false,
      this.snake1.nextMoves = [],
      this.snake2.nextMoves = [],

      this.putApple();
      this.update();
    },
    putApple(){
      var foundPlaceForApple = false;
      var x;
      var y;
      while(foundPlaceForApple != true){
        foundPlaceForApple = true;
        x = Math.floor(Math.random()*(this.mapSize-1-0+1)+0);
        y = Math.floor(Math.random()*(this.mapSize-1-0+1)+0);
        for(var i = this.snake1.snakeLength-1 ; i >= 0 ; i--){
          if(Object(this.snake1.parts[i]).y == y && Object(this.snake1.parts[i]).x == x){   //jeśli złe miejsce
            foundPlaceForApple = false;
            break;
          }
        }
        for(var k = this.snake2.snakeLength-1 ; k >= 0 ; k--){
          if(Object(this.snake2.parts[k]).y == y && Object(this.snake2.parts[k]).x == x){   //jeśli złe miejsce
            foundPlaceForApple = false;
            break;
          }
        }
      }
      this.apple.x = x;
      this.apple.y = y;
    },
  }
}
</script>

<style>
body {
  overflow: hidden;
}
#app {
  font-family: 'Avenir', Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
  margin-top: 60px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}
#map {
  background-size: 40px 40px;
  background-image:
    linear-gradient(to right, grey 1px, transparent 1px),
    linear-gradient(to bottom, grey 1px, transparent 1px);
    position: relative;
}
.snake_part {
  width: 40px;
  height: 40px;
  background-color: green;
  position: absolute;
  border:1px solid green;
  border-radius: 15px;
}
.snake_head {
  width: 40px;
  height: 40px;
  background-color: black;
  position: absolute;
  border:1px solid black;
  border-radius: 15px;
}
.apple {
  width: 40px;
  height: 40px;
  background-color: red;
  position: absolute;
}
p {
  font-family: Sui Generis;
  font-size: 15pt;
}
.md-button.mode {
  margin-top: 20px;
}
</style>
