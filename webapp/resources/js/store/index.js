import { createStore } from "vuex"; // Importa a função createStore do Vuex
import shipHelper from "./../helpers/ships.js"; // Importa ShipHelper para criar features do navio

// Cria e exporta a store Vuex
export default createStore({
    state: {
        ships: [], // Array para armazenar os dados dos navios
        selectedShip: null, // Navio selecionado atualmente
    },
    mutations: {
        // Mutação para adicionar ou atualizar um navio no estado
        addOrUpdateShip(state, ship) {
            const index = state.ships.findIndex((s) => s.mmsi === ship.mmsi);
            ship.features = shipHelper.createShipFeature(ship) || [];

            if (index !== -1) {
                state.ships.splice(index, 1, ship); // Substitui o navio existente
            } else {
                state.ships.push(ship); // Adiciona o novo navio
            }
        },

        // Remove navios inativos
        removeInactiveShips(state, maxInactiveTime) {
            const currentTime = new Date().getTime();
            state.ships = state.ships.filter((ship) => {
                const lastUpdated = new Date(ship.last_updated).getTime();
                return currentTime - lastUpdated <= maxInactiveTime;
            });
        },

        // Define o navio selecionado
        setSelectedShip(state, ship) {
            state.selectedShip = ship;
        },
    },
    actions: {
        // Ação para cometer a mutação addOrUpdateShip
        addOrUpdateShip({ commit }, ship) {
            commit("addOrUpdateShip", ship);
        },

        // Ação para cometer a mutação removeInactiveShips
        removeInactiveShips({ commit }, maxInactiveTime) {
            commit("removeInactiveShips", maxInactiveTime);
        },

        // Ação para definir o navio selecionado
        setSelectedShip({ commit }, ship) {
            commit("setSelectedShip", ship);
        },
    },
    getters: {
        // Getter para recuperar a lista de navios do estado
        getShips(state) {
            return state.ships;
        },

        // Getter para recuperar o navio selecionado
        getSelectedShip(state) {
            return state.selectedShip;
        },
    },
});
