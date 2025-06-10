import moment from "moment";
import "moment/locale/pt";
moment.locale("pt");

export function formatDate(value) {
    if (!value) return "";
    return moment.utc(value).local().format("DD/MM/YYYY");
}

export function formatDateTime(value) {
    if (!value) return "";
    return moment.utc(value).local().format("DD/MM/YYYY HH:mm");
}

export function formatDateTimeSeconds(value) {
    if (!value) return "";
    return moment.utc(value).local().format("DD/MM/YYYY HH:mm:ss");
}
