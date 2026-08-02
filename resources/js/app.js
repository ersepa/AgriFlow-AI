import Alpine from "alpinejs";
import intersect from "@alpinejs/intersect";
import Chart from "chart.js/auto";

window.Chart = Chart;

Alpine.plugin(intersect);

window.Alpine = Alpine;

Alpine.start();

if (document.getElementById("weatherOrb")) {
    import("./weather-orb");
}
