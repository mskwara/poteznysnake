<template>
  <div>
    <md-table v-model="list" md-sort="score" md-sort-order="asc" md-card>
      <md-table-toolbar>
        <md-button class="md-icon-button" @click="switchMode()">
          <md-icon>flip_camera_android</md-icon>
        </md-button>
        <h1 class="md-title">TOP 10</h1>
      </md-table-toolbar>

      <md-table-row slot="md-table-row" slot-scope="{ item }">
        <md-table-cell md-label="Name">{{ item.name }}</md-table-cell>
        <md-table-cell md-label="Score">{{ item.score }}</md-table-cell>
        <md-table-cell md-label="Mode" style="text-transform: uppercase">{{ item.mode }}</md-table-cell>
      </md-table-row>
    </md-table>
  </div>
</template>

<script>
  export default {
    data(){
      return {
        mode: "single",
        oneModeList: [],
      }
    },
    props: ['list'],
    methods: {
      switchMode(){
        if(this.mode == "single"){
          this.mode = "coop";
        }
        else if(this.mode == "coop"){
          this.mode = "single";
        }
        this.$emit('switching', this.mode);
      }
    }
  };
</script>

<style>
.md-table-cell {
  text-align: left;
}
.md-table-row {
  height: 40px !important;
}
.md-table-cell {
  height: 40px !important;
}
.md-title {
  margin-right: 60px !important;
}
</style>
