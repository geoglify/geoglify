const http = require("http");

function startSseServer(clients) {
    const server = http.createServer((req, res) => {
        if (req.url === "/stream") {
            res.writeHead(200, {
                "Content-Type": "text/event-stream",
                "Cache-Control": "no-cache",
                Connection: "keep-alive",
                "Access-Control-Allow-Origin": "*",
            });

            clients.push(res);

            req.on("close", () => {
                const idx = clients.indexOf(res);
                if (idx !== -1) clients.splice(idx, 1);
            });
        } else {
            res.writeHead(404);
            res.end();
        }
    });

    server.listen(9002, () => {
        console.log("SSE server listening on http://localhost:9002/stream");
    });
}

module.exports = { startSseServer };
