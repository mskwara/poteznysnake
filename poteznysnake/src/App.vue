<template>
  <div id="app">
    <p>{{snake}}</p>
    <div id="map" v-bind:style="setMap()">
      <div class="snake_part" :key="part.id" v-for="part in snake" v-bind:style="partPosition(part)"></div>
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
      ],
      alive: true,
      nextId: 1,
      direction: "up",
      snakeLength: 4,
      speed: 300,
      mapSize: 10,
      alreadyTurned: false,
    }
  },
  mounted: function(){
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
    setMap(){
      var mapStyle = {width: this.mapSize*40+1+'px', height: this.mapSize*40+1+'px'};
      return mapStyle;
    },
    update(){
      if(this.alive){
        this.move();
        if(this.snake[0].x == -1 || this.snake[0].x == this.mapSize || this.snake[0].y == -1 || this.snake[0].y == this.mapSize){
          this.alive = false;
        }
        setTimeout(this.update, this.speed);
      }
      else {
        alert("GAME OVER");
        this.reset();
      }
      this.alreadyTurned = false;
    },
    move(){
      for(var i = this.snakeLength ; i > 0 ; i--){      //ruch całego ciała
        Object(this.snake[i]).y = this.snake[i-1].y;
        Object(this.snake[i]).x = this.snake[i-1].x;
      }

      if(this.direction == "up"){     //ruchy głowy
        this.snake[0].y--;
      }
      else if(this.direction == "right"){
        this.snake[0].x++;
      }
      else if(this.direction == "down"){
        this.snake[0].y++;
      }
      else if(this.direction == "left"){
        this.snake[0].x--;
      }
    },
    turn(dir){
      if(!this.alreadyTurned){
        if(this.direction == "up" && dir == "down") return;
        if(this.direction == "right" && dir == "left") return;
        if(this.direction == "down" && dir == "up") return;
        if(this.direction == "left" && dir == "right") return;
        this.direction = dir;
        this.alreadyTurned = true;
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
      ];
      this.update();
    }
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
</style>
