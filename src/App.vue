<template>
  <div id="app">
    <div class="scores">
      <p v-if="gameMode == 'coop' || gameMode == 'single'" style="margin-left:20px">Liczba punktów: {{snake1.points+snake2.points}}</p>
      <p v-if="gameMode == 'battle'" style="margin-left:20px">Gracz 1: {{snake1.points}} punktów<br><br>Gracz 2: {{snake2.points}} punktów</p>
    </div>
    <div id="map" v-bind:style="setMap()">
      <p class="sleepTimer" v-if="sleeping > 0">{{sleeping}}</p>
      <div :class="snakeClass(part.id, snake1)" :key="snake1.snakeId+part.id" v-for="part in snake1.parts" v-bind:style="partPosition(part)"></div>
      <div v-if="gameMode != 'single'"><div :class="snakeClass(part.id, snake2)" :key="snake2.snakeId+part.id" v-for="part in snake2.parts" v-bind:style="partPosition(part)"></div></div>
      <div class="apple" v-bind:style="applePosition()"></div>
    </div>

    <div class="menuPanel">
      <md-list class="md-double-line">
      <md-subheader>Ustawienia</md-subheader>

      <md-list-item>
        <md-icon class="md-primary">extension</md-icon>

        <div class="md-list-item-text">
          <span style="text-transform: uppercase">{{gameMode}}</span>
          <span>Tryb gry</span>
        </div>

        <md-button class="md-primary md-raised" @click="changeMode()" :disabled="alive">Zmień</md-button>
      </md-list-item>
      <md-list-item>
        <md-icon class="md-primary">photo_size_select_small</md-icon>

        <div class="md-list-item-text">
          <span>
            <range-slider
              class="slider"
              :disabled="alive"
              min="8"
              max="16"
              step="1"
              v-model="mapSize">
            </range-slider>{{mapSize}}</span>
          <span>Rozmiar mapy</span>
        </div>


      </md-list-item>
      <md-list-item>
        <md-icon class="md-primary">cached</md-icon>

        <div class="md-list-item-text">
          <span>Restart</span>
        </div>

        <md-button class="md-primary md-raised" @click="reset()">Restart</md-button>
      </md-list-item>

    </md-list>

    </div>


    <md-dialog-confirm
      :md-active.sync="gameOverAlert"
      md-title="GAME OVER"
      :md-content="alertText"
      md-confirm-text="Jeszcze raz"
      @md-confirm="reset" />

    <md-dialog-confirm
      :md-active.sync="victory"
      md-title="WYGRAŁEŚ"
      md-content="Jesteś mistrzem tej gry! Gratulacje!"
      md-confirm-text="Jeszcze raz"
      @md-confirm="reset" />

  </div>
</template>

<script>
import RangeSlider from 'vue-range-slider'
// you probably need to import built-in style
import 'vue-range-slider/dist/vue-range-slider.css'

export default {
  components: {RangeSlider},
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
          color: "green",
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
          color: "orange",
      },
      gameMode: "coop",
      alive: false,
      speed: 200,
      mapSize: 10,
      apple: {
        x: 8,
        y: 8
      },
      sleeping: 3,
      pointsFactor: 1,
      applesCount: 0,
      whyDied: "",
      gameOverAlert: false,
      alertText: "Koniec gry",
      victory: false,
    }
  },

  mounted: function(){
    this.sleep(3);
    this.putApple();
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
        this.gameMode = "single";
      }
      else if(this.gameMode == "single"){
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
    snakeClass(id, snake){
      if(snake.color == "green"){
        if(id == 0){
          return 'snake_head';
        }
        else return 'snake_part1';
      }
      else if(snake.color == "orange"){
        if(id == 0){
          return 'snake_head';
        }
        else return 'snake_part2';
      }

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
        if(this.applesCount == this.mapSize*this.mapSize-5){
          this.victory = true;
          this.alive = false;
        }
        this.putApple();
      }
    },
    update(){
      if(this.alive){
        this.move(this.snake1);
        if(this.gameMode != "single")   this.move(this.snake2);
        this.checkSnakeAlive(this.snake1);
        if(this.gameMode != "single")   this.checkSnakeAlive(this.snake2);
        if(this.gameMode != "single")   this.checkSnakeEatSnake();

        if(!this.alive){
          this.gameOver();
        }
        this.checkAppleEaten(this.snake1);
        if(this.gameMode != "single")   this.checkAppleEaten(this.snake2);

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
      this.alive = false;
      this.victory = false;
      this.snake1.direction = "right";
      this.snake2.direction = "right";
      this.snake1.parts = [
        {id: 0, x: 4, y: 6},
        {id: 1, x: 3, y: 6},
        {id: 2, x: 2, y: 6},
        {id: 3, x: 1, y: 6},
        {id: 4, x: 0, y: 6},
      ];
      this.snake2.parts = [
        {id: 0, x: 4, y: 2},
        {id: 1, x: 3, y: 2},
        {id: 2, x: 2, y: 2},
        {id: 3, x: 1, y: 2},
        {id: 4, x: 0, y: 2},
      ];
      this.snake1.snakeLength = 5;
      this.snake2.snakeLength = 5;
      this.snake1.isAppleEaten = false;
      this.snake2.isAppleEaten = false;
      this.snake1.points = 0;
      this.snake2.points = 0;
      this.speed = 200;
      this.pointsFactor = 1;

      this.snake1.nextId = 5;
      this.snake2.nextId = 5;
      this.snake1.alreadyTurned = false;
      this.snake2.alreadyTurned = false;
      this.snake1.nextMoves = [];
      this.snake2.nextMoves = [];
      this.applesCount = 0;

      this.putApple();
      this.sleep(3);
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
        if(this.gameMode != "single"){
          for(var k = this.snake2.snakeLength-1 ; k >= 0 ; k--){
            if(Object(this.snake2.parts[k]).y == y && Object(this.snake2.parts[k]).x == x){   //jeśli złe miejsce
              foundPlaceForApple = false;
              break;
            }
          }
        }
      }
      this.apple.x = x;
      this.apple.y = y;
    },
    sleep(sec){
      this.sleeping = sec;
      if(this.sleeping > 0){
        setTimeout(()=>{
          this.sleeping-=1;
          this.sleep(this.sleeping);
        }, 1000);
      }
      if(this.sleeping == 0) {
        this.alive = true;
        this.update();
      }
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
  flex-direction: row;
  align-items: flex-start;
  justify-content: space-between;
}
#map {
  background-size: 40px 40px;
  background-image:
    linear-gradient(to right, grey 1px, transparent 1px),
    linear-gradient(to bottom, grey 1px, transparent 1px);
    position: relative;
}
.snake_part1 {
  width: 40px;
  height: 40px;
  background-color: green;
  position: absolute;
  border:1px solid green;
  border-radius: 15px;
}
.snake_part2 {
  width: 40px;
  height: 40px;
  background-color: orange;
  position: absolute;
  border:1px solid orange;
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
.sleepTimer {
  font-size: 100pt;
  font-family: Sui Generis;
  color: yellow;
  text-align: center;
  padding-top: 40px;
}
.menuPanel {
  width: 350px;
}
.range-slider-knob {
  width: 20px !important;
}
.scores {
  width: 300px;
}
</style>
