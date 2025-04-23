<script>
var i = 0;
var client = new Messaging.Client("13.214.212.87", 9001, "myclientid_" + parseInt(Math.random() * 100, 10));
//a = new AudioContext(); // browsers limit the number of concurrent audio contexts,

client.onConnectionLost = function (responseObject) {
    toastr.error('Trying to reconnect...', 'Server not responding.', { closeButton: true, timeOut: 3000, progressBar: true, allowHtml: true });
    MQTTreconnect();
};

function MQTTreconnect() {
    if (client.connected) {
        return;
    }
    //console.log("ATTEMPTING TO RECONNECT");
    // Set a timeout before attempting to reconnect
    setTimeout(function () {
        // Try to reconnect
        client.connect(options);
    }, 5000); // You can adjust the timeout duration as needed
}

//Connect Options
var options = {
    timeout: 3,
    keepAliveInterval: 60,
    userName: 'mqtt',
    password: 'ICPHmqtt!',
    onSuccess: function () {
        client.subscribe('AERIS/DataUpdate', {qos: 0});
        client.subscribe('AERIS/FootTrafficUpdate', {qos: 0});
        client.subscribe('AERIS/MapUpdate', {qos: 0});
        toastr.success('','Server OK!');

    var message = new Messaging.Message('New Message');
    message.destinationName = 'AERIS/newTopicTest';
    message.qos = 0;
    client.send(message);
    },

    onFailure: function (message) {
        //beeps(10,1000,1000);
        toastr.error('Trying to reconnect...', 'Server not responding.', { closeButton: true, timeOut: 3000, progressBar: true, allowHtml: true });
        MQTTreconnect();
    }

};

var publish = function (payload, topic, qos) {
    var message = new Messaging.Message(payload);
    message.destinationName = topic;
    message.qos = qos;
    client.send(message);
}

// client.subscribe('AERIS/DataUpdate', {qos: 0});
//         client.subscribe('AERIS/FootTrafficUpdate', {qos: 0});
//         client.subscribe('AERIS/MapUpdate', {qos: 0});
client.onMessageArrived = function (message) {
    var x = message.payloadString;
    console.log("UPDATE RECEIVED");
    console.log(x);
    //a = new AudioContext(); // browsers limit the number of concurrent audio contexts,
    if (message.destinationName == "AERIS/DataUpdate") {
      console.log("SENSOR DATA UPDATE");
      displaySensorDatas(x);
      loadTableData();
    } else if (message.destinationName == "AERIS/FootTrafficUpdate") {
      console.log("FOOT TRAFFIC UPDATE");
      displayFootTraffic(x);
    } else if (message.destinationName == "AERIS/MapUpdate") {
      console.log("MAP UPDATE");
      goToMarker(x);
    }
}

</script>