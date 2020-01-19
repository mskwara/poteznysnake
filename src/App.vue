<template>
  <div id="app">
    <p>Liczba punktów: {{points}}</p>
    <div id="map" v-bind:style="setMap()">
      <div class="snake_part" :key="part.id" v-for="part in snake" v-bind:style="partPosition(part)"></div>
      <div class="apple" v-bind:style="applePosition()"></div>
    </div>
  </div>
</template>

<script>

export default {
  data(){
    return {
      SIZE: 40,
      snake: [
        {id: 0, x: 6, y: 6},
        {id: 1, x: 5, y: 6},
        {id: 2, x: 4, y: 6},
        {id: 3, x: 3, y: 6},
        {id: 4, x: 2, y: 6},
      ],
      alive: true,
      nextId: 5,
      direction: "up",
      snakeLength: 5,
      speed: 250,
      mapSize: 15,
      alreadyTurned: false,
      nextMoves: [],
      apple: {
        x: 8,
        y: 8
      },
      isAppleEaten: false,
      points: 0,
      pointsFactor: 1,
      applesCount: 0,
    }
  },
  mounted: function(){
    this.putApple();
    this.update();
    window.addEventListener("keyup", e => {
      if(e.keyCode == 37){  //left
        this.turn("left");
      }
      if(e.keyCode == 38){  //up
        this.turn("up");
      }
      if(e.keyCode == 39){  //right
        this.turn("right");
      }
      if(e.keyCode == 40){  //down
        this.turn("down");
      }
    });

  },
  methods: {
    partPosition(part) {
      var positionStyle = {top: this.SIZE*part.y+'px', left: this.SIZE*part.x+'px'};
      return positionStyle;
    },
    applePosition() {
      var positionStyle = {top: this.SIZE*this.apple.y+'px', left: this.SIZE*this.apple.x+'px'};
      return positionStyle;
    },
    setMap(){
      var mapStyle = {width: this.mapSize*40+1+'px', height: this.mapSize*40+1+'px'};
      return mapStyle;
    },
    update(){
      if(this.alive){
        this.move();

        if(this.snake[0].x == -1 || this.snake[0].x == this.mapSize || this.snake[0].y == -1 || this.snake[0].y == this.mapSize){ //za mapą
          this.alive = false;
        }
        else {
          for(var i = this.snakeLength-1 ; i > 0 ; i--){      //czy uderza w ogon
            if(Object(this.snake[i]).y == this.snake[0].y && Object(this.snake[i]).x == this.snake[0].x){
              this.alive = false;
              break;
            }
          }
        }
        if(!this.alive){
          this.gameOver();
        }

        if(this.snake[0].x == this.apple.x && this.snake[0].y == this.apple.y){
          this.isAppleEaten = true;
          this.points += 10*this.pointsFactor;
          this.applesCount++;
          if(this.applesCount%5 == 0){
            this.pointsFactor += 1;
            this.speed -= 20;
          }
          this.putApple();
        }

        setTimeout(this.update, this.speed);
      }
      else {
        this.reset();
      }
    },
    gameOver(){
      alert("GAME OVER\nIlość punktów: "+this.points);
    },
    move(){
      if(this.isAppleEaten == true){
        var newPart = {};
        newPart.id = this.nextId;
        newPart.x = Object(this.snake[this.snakeLength-1]).x;
        newPart.y = Object(this.snake[this.snakeLength-1]).y;
        this.snake.push(newPart);
        this.snakeLength++;
        this.nextId++;
        this.isAppleEaten = false;
      }
      for(var i = this.snakeLength-1 ; i > 0 ; i--){      //ruch całego ciała
        Object(this.snake[i]).y = this.snake[i-1].y;
        Object(this.snake[i]).x = this.snake[i-1].x;
      }
      if(this.nextMoves.length != 0){
        if(this.nextMoves[0] == "up"){     //ruchy głowy
          this.snake[0].y--;
        }
        else if(this.nextMoves[0] == "right"){
          this.snake[0].x++;
        }
        else if(this.nextMoves[0] == "down"){
          this.snake[0].y++;
        }
        else if(this.nextMoves[0] == "left"){
          this.snake[0].x--;
        }
        this.nextMoves.splice(0,1);
      }
      else {
        if(this.direction == "up"){     //ruchy głowy
          this.snake[0].y--;
        }
        else if(this.direction == "right"){
          this.snake[0].x++;
        }
        else if(this.direction == "down"){
          this.snake[0].y++;
        }
        else if(this.direction   == "left"){
          this.snake[0].x--;
        }
      }
    },
    turn(dir){
      if(this.direction == "up" && dir == "down") return;
      if(this.direction == "right" && dir == "left") return;
      if(this.direction == "down" && dir == "up") return;
      if(this.direction == "left" && dir == "right") return;
      if(this.nextMoves.length < 2){
        this.direction = dir;
        this.nextMoves.push(dir);
      }
    },
    reset(){
      this.alive = true;
      this.direction = "up";
      this.snake = [
        {id: 0, x: 6, y: 6},
        {id: 1, x: 5, y: 6},
        {id: 2, x: 4, y: 6},
        {id: 3, x: 3, y: 6},
        {id: 4, x: 2, y: 6},
      ];
      this.snakeLength = 5;
      this.isAppleEaten = false;
      this.points = 0;
      this.speed = 250;
      this.pointsFactor = 1;
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
        for(var i = this.snakeLength-1 ; i > 0 ; i--){      //czy uderza w ogon
          if(Object(this.snake[i]).y == y && Object(this.snake[i]).x == x){   //jeśli złe miejsce
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
</style>
