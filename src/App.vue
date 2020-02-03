<template>
  <div id="app">
    <h6 class="pause" v-if="pause == true" :style="'left: '+(this.mapSize/2-1)*this.SIZE+'px'">{{pauseText}}</h6>
    <div class="mainView">

      <div class="scores">
        <p v-if="gameMode == 'coop' || gameMode == 'single'" style="margin-left:20px">Liczba punktów: {{snake1.points+snake2.points}}</p>
        <p v-if="gameMode == 'battle'" style="margin-left:20px">Gracz 1: {{snake1.points}} punktów<br><br>Gracz 2: {{snake2.points}} punktów</p>
        <scoreboard :list="ranking"></scoreboard>
      </div>
      <div id="map" v-bind:style="setMap()">
        <p class="sleepTimer" v-if="sleeping > 0">{{sleeping}}</p>

        <div :class="snakeClass(part.id, snake1)" :key="snake1.snakeId+part.id" v-for="part in snake1.parts" v-bind:style="partPosition(part)"></div>
        <div v-if="gameMode != 'single'"><div :class="snakeClass(part.id, snake2)" :key="snake2.snakeId+part.id" v-for="part in snake2.parts" v-bind:style="partPosition(part)"></div></div>
        <div class="apple" v-bind:style="applePosition(apple)" v-if="apple.visible == true">
          <md-icon class="fruit" :md-src="require('./assets/apple.svg')" />
        </div>
        <div v-if="animal.visible == true">
          <div class="animal" v-bind:style="applePosition(animal)">
            <div class="animalTimer">{{animal.remainingTime}}</div>
            <md-icon class="fruit" :md-src="require('./assets/animal'+this.setAnimalImage()+'.svg')" />
          </div>
        </div>
        <div v-if="poison.visible == true">
          <div class="animal" v-bind:style="applePosition(poison)">
            <div class="animalTimer">{{poison.remainingTime}}</div>
            <md-icon class="fruit" :md-src="require('./assets/skull.svg')" />
          </div>
        </div>
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
                </range-slider></span>
                <span>Rozmiar mapy</span>
              </div>
            </md-list-item>

            <md-list-item>
              <md-icon class="md-primary">speed</md-icon>

              <div class="md-list-item-text">
                <span class="sliderSpan">
                  <range-slider
                    class="slider"
                    :disabled="alive"
                    min="-100"
                    max="70"
                    step="10"
                    v-model="speedFactor">
                  </range-slider></span>
                  <span>Prędkość</span>
                </div>
              </md-list-item>

            <md-list-item>
              <md-icon class="md-primary">cached</md-icon>

              <div class="md-list-item-text">
                <span>Restart</span>
              </div>

              <md-button class="md-primary md-raised" @click="reset()">Restart</md-button>
            </md-list-item>

            <md-list-item>
              <md-icon class="md-primary">help_outline</md-icon>
              <md-button class="md-primary md-raised btnHelp" @click="help = true">Pomoc</md-button>
            </md-list-item>

            <md-list-item>
              <md-icon class="md-primary">fingerprint</md-icon>

              <div class="md-list-item-text">
                <span class="copyright">Game created by Michał Skwara</span>
              </div>

            </md-list-item>

          </md-list>
        </div>

        <md-dialog :md-active.sync="help">
          <md-dialog-title>Pomoc</md-dialog-title>

          <md-tabs md-dynamic-height>
            <md-tab md-label="Tryby">
              <p>Gra umożliwia wybór trzech trybów rozgrywki. Są to tzw. SINGLE, w którym twoim celem jest zjedzenie jak największej ilości jabłek. Zbieraj punkty i staraj się nie zjeść własnego ogona!</p>
              <p>Kolejnym trybem jest COOP - współpracuj ze znajomym by uzyskać jak najwyższy wynik. Tylko rozsądnie dobrana taktyka zapewni Ci pierwsze miejsce w tabeli!</p>
              <p>Ostatni tryb to BATTLE, którego wynik nie jest zapisywany do tablicy wyników. Zmierz się ze swoim znajomym w emocjonującej rozgrywce. To umiejętności zdecydują kto zasługuje na wygraną!</p>
            </md-tab>

            <md-tab md-label="Dodatki">
              <p>Poza jabłkami na planszy możesz spotkać jeszcze parę niespodzianek. Jedną z nich jest zwierzątko. Jest ono warte więcej punktów niż zwykłe jabłko, a każde kolejne jest smaczniejsze niż poprzednie. Staraj się żadnego nie przeoczyć!</p>
              <p>Jeżeli na swojej drodze zobaczysz truciznę, omijaj ją szerokim łukiem. Ma negatywny wpływ na węża oraz na twoją ambicję zajęcia pierwszego miejsca w tabeli!</p>
            </md-tab>

            <md-tab md-label="Ustawienia">
              <p>Zdobycie najlepszego wyniku nie należy do najproszych. Wymaga skupienia i poświęcenia czasu na rozgrywkę. Możesz jednak zwiększyć swoje osiągi poprzez podkręcenie prędkości węża lub zmianę wielkości mapy.</p>
              <p>Wybierz najlepsze według Ciebie ustawienia i rozpocznij rozgrywkę!</p>
            </md-tab>
          </md-tabs>

          <md-dialog-actions>
            <md-button class="md-primary" @click="help = false">Zamknij</md-button>
          </md-dialog-actions>
        </md-dialog>


      </div>

      <md-dialog-confirm
        :md-active.sync="gameOverAlert"
        md-title="GAME OVER"
        :md-content="alertText"
        md-confirm-text="Jeszcze raz"
        md-cancel-text="Chcę zapisać wynik"
        @md-confirm="reset"
        @md-cancel="wannaSave()" />

      <md-snackbar :md-duration="duration" :md-active.sync="snackbarError" md-persistent>
        Zdobądź więcej punktów lub zmień tryb gry!
      </md-snackbar>

      <md-dialog-confirm
        :md-active.sync="victory"
        md-title="WYGRAŁEŚ"
        md-content="Jesteś mistrzem tej gry! Gratulacje!"
        md-confirm-text="Jeszcze raz"
        md-cancel-text="Chcę zapisać wynik"
        @md-confirm="reset"
        @md-cancel="wannaSave()" />

        <md-dialog-prompt
          :md-active.sync="saving"
          v-model="user.name"
          md-title="Zapisz swój wynik"
          md-input-maxlength="15"
          md-input-placeholder="Podaj swój nick..."
          md-cancel-text="Powrót"
          md-confirm-text="Zapisz"
          @md-confirm="postScore" />

        </div>




</template>

<script>
import RangeSlider from 'vue-range-slider'
import Scoreboard from './Scoreboard.vue'
// you probably need to import built-in style
import 'vue-range-slider/dist/vue-range-slider.css'

export default {
  components: {RangeSlider, Scoreboard},
  data(){
    return {
      SIZE: 40,
      duration: 3000,
      ranking: [],
      snackbarError: false,
      pause: false,
      pauseText: "PAUSE",
      help: false,
      user: {
        name: "",
        score: 0,
        mode: ""
      },
      saving: false,
      snake1: {
        snakeId: "1",
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
        isAnimalEaten: false,
        isPoisonEaten: false,
        points: 0,
        color: "green",
      },
      snake2: {
        snakeId: "2",
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
        isAnimalEaten: false,
        isPoisonEaten: false,
        points: 0,
        color: "orange",
      },
      gameMode: "single",
      alive: false,
      speed: 200,
      speedFactor: 0,
      mapSize: 10,
      apple: {
        x: 8,
        y: 8,
        visible: false
      },
      animal: {
        x: -2,
        y: -2,
        value: 0,
        visible: false,
        wasDisplayed: false,
        eaten: false,
        time: 3,
        remainingTime: 3,
      },
      poison: {
        x: -3,
        y: -3,
        value: 50,
        visible: false,
        wasDisplayed: false,
        eaten: false,
        time: 5,
        remainingTime: 5,
        next: 16,
      },
      sleeping: 3,
      pointsFactor: 1,
      applesCount: 0,
      whyDied: "",
      gameOverAlert: false,
      alertText: "Koniec gry",
      victory: false,
      scoreboard: "",
    }
  },

  mounted: function(){
    this.getScoreboard();
    setInterval(this.getScoreboard, 5000);
    this.sleep(3);
    window.addEventListener("keyup", e => {
      if(e.keyCode == 80 && this.saving == false){  //p
        this.pause == false ? this.pause = true : this.pause = false;
      }
      if(this.pause == false){
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
      }
    });

  },
methods: {
  getScoreboard(){
    this.$http.get('scoreboard').then(response => {
      this.ranking = response.body;
    });
  },
  setAnimalImage(){
    if(this.animal.value/15 <= 8){
      return this.animal.value/15;
    }
    if(this.animal.value/15 > 8){
      return 8;
    }
  },
  wannaSave(){
    if(this.snake1.points + this.snake2.points != 0 && this.gameMode != 'battle') this.saving=true;
    else this.snackbarError = true;
  },
  saveBtn(){
    this.saving = true;
  },
  postScore(){
    if(this.gameMode == "single"){
      this.user.score = this.snake1.points;
    }
    if(this.gameMode == "coop"){
      this.user.score = this.snake1.points + this.snake2.points;
    }
    this.user.mode = this.gameMode;
    this.$http.post('addscore', this.user);
    this.user.name = "";
    this.user.score = 0;
    this.user.mode = "";
    this.getScoreboard();
  },
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
  applePosition(apple) {
    var positionStyle = {top: this.SIZE*apple.y+'px', left: this.SIZE*apple.x+'px'};
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
      this.putApple("apple");
    }
  },
  checkAnimalEaten(snake){
    if(snake.parts[0].x == this.animal.x && snake.parts[0].y == this.animal.y){
      snake.isAnimalEaten = true;
      snake.points += this.animal.value*this.pointsFactor;
      this.applesCount++;
      this.animal.x = -2;
      this.animal.y = -2;
      this.animal.visible = false;
      this.animal.eaten = true;
      if(this.applesCount == this.mapSize*this.mapSize-5){
        this.victory = true;
        this.alive = false;
      }
    }
  },
  checkPoisonEaten(snake){
    if(snake.parts[0].x == this.poison.x && snake.parts[0].y == this.poison.y){
      snake.isPoisonEaten = true;
      snake.points -= this.poison.value*this.pointsFactor;
      this.applesCount++;
      this.poison.x = -3;
      this.poison.y = -3;
      this.poison.visible = false;
      this.poison.eaten = true;
      if(this.applesCount == this.mapSize*this.mapSize-5){
        this.victory = true;
        this.alive = false;
      }
      this.invertSnake(snake);
    }
  },
  update(){
    if(this.alive){
      if(!this.pause){
        this.move(this.snake1);
        if(this.gameMode != "single")   this.move(this.snake2);
        this.checkSnakeAlive(this.snake1);
        if(this.gameMode != "single")   this.checkSnakeAlive(this.snake2);
        if(this.gameMode != "single")   this.checkSnakeEatSnake();

        if(!this.alive){
          this.gameOver();
        }
        this.checkAppleEaten(this.snake1);
        this.checkAnimalEaten(this.snake1);
        this.checkPoisonEaten(this.snake1);
        if(this.gameMode != "single"){
          this.checkAppleEaten(this.snake2);
          this.checkAnimalEaten(this.snake2);
          this.checkPoisonEaten(this.snake2);
        }

        if(this.applesCount != 0 && this.applesCount%10 == 0 && this.animal.visible == false && this.animal.wasDisplayed == false){
          this.putApple("animal");
          var time = this.animal.time;
          this.animal.remainingTime = time;
          this.controlAnimalTime(time);
        }
        if(this.applesCount != 0 && this.applesCount == this.poison.next && this.poison.visible == false && this.poison.wasDisplayed == false){
          this.putApple("poison");
          var time1 = this.poison.time;
          this.poison.remainingTime = time1;
          this.controlPoisonTime(time1);
        }
      }
      setTimeout(this.update, this.speed);
    }

  },
  controlAnimalTime(time){
    if(time > 0){
      if(this.animal.eaten == true){
        this.animal.visible = false;
        this.animal.x = -2;
        this.animal.y = -2;
      }
      if(this.animal.eaten == false){
        setTimeout(()=>{
          if(this.animal.eaten == false && this.pause == false){
            time-=1;
            this.animal.remainingTime -= 1;
          }
          this.controlAnimalTime(time);
        }, 1000);
      }
    }
    if(time == 0) {
      this.animal.x = -2;
      this.animal.y = -2;
      this.animal.value = 0;
      this.animal.visible = false;
    }
  },
  controlPoisonTime(time){
    if(time > 0){
      if(this.poison.eaten == true){
        this.poison.visible = false;
        this.poison.x = -3;
        this.poison.y = -3;
      }
      if(this.poison.eaten == false){
        setTimeout(()=>{
          if(this.poison.eaten == false && this.pause == false){
            time-=1;
            this.poison.remainingTime -= 1;
          }
          this.controlPoisonTime(time);
        }, 1000);
      }
    }
    if(time == 0) {
      this.poison.x = -3;
      this.poison.y = -3;
      this.poison.visible = false;
    }
  },
  gameOver(){
    this.alertText = "Koniec gry. Wynik: "+this.snake1.points;
    if(this.gameMode == "battle"){
      if(this.snake1.points > this.snake2.points){
        this.alertText+=". Wygrywa gracz 1";
      }
      if(this.snake1.points < this.snake2.points){
        this.alertText+=". Wygrywa gracz 2";
      }
      if(this.snake1.points == this.snake2.points){
        this.alertText+=". Remis";
      }
    }
    this.gameOverAlert = true;
  },
  move(snake){
    if(snake.isAppleEaten == true || snake.isAnimalEaten == true || snake.isPoisonEaten){
      var newPart = {};
      newPart.id = snake.nextId;
      newPart.x = Object(snake.parts[snake.snakeLength-1]).x;
      newPart.y = Object(snake.parts[snake.snakeLength-1]).y;
      snake.parts.push(newPart);
      snake.snakeLength++;
      snake.nextId++;
      if(snake.isAppleEaten == true){
        snake.isAppleEaten = false;

      }
      else if(snake.isAnimalEaten == true){
        snake.isAnimalEaten = false;
      }
      else if(snake.isPoisonEaten == true){
        snake.isPoisonEaten = false;
      }
      if(this.applesCount%10 != 0)  this.animal.wasDisplayed = false;
      if(this.applesCount != this.poison.next)  {
        if(this.poison.wasDisplayed == true || (this.poison.eaten == true && this.poison.wasDisplayed == true)){
          this.poison.next += Math.floor(Math.random() * (25 - 14 + 1) ) + 14;
        }
        this.poison.wasDisplayed = false;
      }
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
    this.snake1.isAnimalEaten = false;
    this.snake2.isAnimalEaten = false;
    this.snake1.isPoisonEaten = false;
    this.snake2.isPoisonEaten = false;
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
    this.apple.visible = false;

    this.animal.x = -2;
    this.animal.y = -2;
    this.animal.value = 0;
    this.animal.visible = false;
    this.animal.wasDisplayed = false;
    this.animal.eaten = false;

    this.poison.x = -3,
    this.poison.y = -3,
    this.poison.value = 150,
    this.poison.visible = false,
    this.poison.wasDisplayed = false,
    this.poison.eaten = false,
    this.poison.time = 5,
    this.poison.remainingTime = 5,


    this.user.name = "";
    this.user.score = 0;
    this.user.mode = "";

    this.sleep(3);
  },
  setAnimalTime(){
    if(this.mapSize <= 11)  this.animal.time = 3;
    if(this.mapSize >= 12 && this.mapSize <= 15)  this.animal.time = 4;
    if(this.mapSize >= 16)  this.animal.time = 5;
    if(this.speedFactor >= 50){
      this.animal.time -= 1;
    }
  },
  putApple(obj){
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
      if(obj == "apple" && (x == this.animal.x && y == this.animal.y) || (x == this.poison.x && y == this.poison.y)){
        foundPlaceForApple = false;
      }
      else if(obj == "animal" && (x == this.apple.x && y == this.apple.y) || (x == this.poison.x && y == this.poison.y)){
        foundPlaceForApple = false;
      }
      else if(obj == "poison" && (x == this.apple.x && y == this.apple.y) || (x == this.animal.x && y == this.animal.y)){
        foundPlaceForApple = false;
      }
    }
    if(obj == "apple"){
      this.apple.x = x;
      this.apple.y = y;
      this.apple.visible = true;
    }
    else if(obj == "animal"){
      this.animal.x = x;
      this.animal.y = y;
      this.animal.value += 15;
      this.animal.visible = true;
      this.animal.wasDisplayed = true;
      this.animal.eaten = false;
    }
    else if(obj == "poison"){
      this.poison.x = x;
      this.poison.y = y;
      this.poison.visible = true;
      this.poison.wasDisplayed = true;
      this.poison.eaten = false;
    }

  },
  invertSnake(snake){
    this.pause = true;
    this.pauseText = "";
    for(var i = 0 ; i < snake.snakeLength/2 ; i++){  //zamiana części węża żeby szedł w druga strone
      var x1 = snake.parts[i].x;
      var y1 = snake.parts[i].y;
      snake.parts[i].x = snake.parts[snake.snakeLength-1-i].x;
      snake.parts[i].y = snake.parts[snake.snakeLength-1-i].y;
      snake.parts[snake.snakeLength-1-i].x = x1;
      snake.parts[snake.snakeLength-1-i].y = y1;
    }
    snake.alreadyTurned = false;
    snake.nextMoves = [];
    if(snake.parts[0].x == snake.parts[1].x){ //góra - dół
      if(snake.parts[0].y < snake.parts[1].y){
        snake.direction = "up";
      }
      else {
        snake.direction = "down";
      }
    }
    else if(snake.parts[0].y == snake.parts[1].y){ //lewo-prawo
      if(snake.parts[0].x < snake.parts[1].x){
        snake.direction = "left";
      }
      else {
        snake.direction = "right";
      }
    }
    setTimeout(()=>{
      this.pause = false;
      this.pauseText = "PAUSE";
    }, 1000);
  },
  sleep(sec){
    this.sleeping = sec;
    if(this.sleeping > 0){
      setTimeout(()=>{
        if(this.pause == false) this.sleeping-=1;
        this.sleep(this.sleeping);
      }, 1000);
    }
    if(this.sleeping == 0) {
      this.setAnimalTime();
      this.poison.next = Math.floor(Math.random() * (25 - 14 + 1) ) + 14;
      this.alive = true;
      this.putApple("apple");
      this.speed -= this.speedFactor;
      if(this.speedFactor > 0){
        this.pointsFactor += this.speedFactor/100;
      }
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
  flex-direction: column;
  align-items: stretch;

  flex-wrap: wrap;
}
#map {
  background-size: 40px 40px;
  background-image:
  linear-gradient(to right, grey 1px, transparent 1px),
  linear-gradient(to bottom, grey 1px, transparent 1px);
  position: relative;
}
.mainView {
  display: flex;
  flex-direction: row;
  align-items: flex-start;
  justify-content: space-between;
  flex-wrap: wrap;
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
  position: absolute;
  display: flex;
  align-items: center;
}
.animal {
  width: 40px;
  height: 40px;
  position: absolute;
  display: flex;
  align-items: center;
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
  color: #00c3ff;
  text-align: center;
  padding-top: 40px;
}
.menuPanel {
  width: 350px;
}
.range-slider-knob {
  width: 20px !important;
  background-color: #63bd4a;
}
.scores {
  width: 450px;
  margin-left: 20px;
}
.copyright {
  color: grey;
  font-size: 10pt;
}
.pause {
  font-size: 15pt;
  font-family: Sui Generis;
  color: brown;
  padding-top: 0;
  margin-top: 0;
  position: absolute;
  top: 0;
}
.animalTimer {
  position: absolute;
  left: 2px;
  top: 0px;
  font-size: 7pt;
  font-family: Sui Generis;
}
.md-tab p {
  font-family: Calibri;
}
.btnHelp {
  background-color: #e945ff !important;
  width: 100%;
}
</style>
