<template>
  <v-dialog v-model="dialogVisible" max-width="500px" persistent scrollable>
    <v-card>
      <v-card-title class="font-weight-bold py-4">
        Filter Ships by Cargo
      </v-card-title>

      <v-divider></v-divider>

      <v-card-text style="height: 400px" class="ma-0 pa-0">
        <v-list density="compact" class="ma-0 pa-0">
          <v-list-item v-for="item in cargos" :key="item.id">
            <template v-slot:prepend>
              <v-list-item-action start>
                <v-checkbox-btn v-model="item.is_active"></v-checkbox-btn>
              </v-list-item-action>
            </template>

            <v-list-item-title class="text-subtitle-1">{{ item.name }}</v-list-item-title>

            <template v-slot:append>
              <v-icon :color="item.color">mdi-label</v-icon>
            </template>
          </v-list-item>
        </v-list>
      </v-card-text>

      <v-divider></v-divider>

      <v-card-actions>
        <v-btn text @click="toggleAll">{{ toggleAllText }}</v-btn>
        <v-spacer></v-spacer>
        <v-btn text @click="close">Close</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
  
</template>

<script>
export default {
  props: ["open"],
  data() {
    return {
      dialogVisible: false,
    };
  },
  computed: {
    cargos() {
      return this.$store.state.ships.cargos;
    },
    toggleAllText() {
      const allSelected = this.cargos.every(item => item.is_active);
      return allSelected ? "Deselect All" : "Select All";
    }
  },
  watch: {
    open(value) {
      this.dialogVisible = value;
    },
    dialogVisible(value) {
      this.$emit("update:open", value);
    }
  },
  methods: {
    close() {
      this.dialogVisible = false;
    },
    toggleAll() {
      const allSelected = this.cargos.every(item => item.is_active);
      this.cargos.forEach(item => {
        item.is_active = !allSelected;
      });
    }
  }
};
</script>
