#include <Servo.h> 

Servo waver, dancer;

// Analog Pins free inventory: 0,1,2,3,4,5

// Digital Pins for Input
const int buttonPin = 2;

// Digital Pins for Output
const int heartPin3 = 3;
const int heartPin4 = 4;
const int heartPin5 = 5;
const int goPin     = 6;
const int wavePin   = 7;
//const int  = 8;
const int blinkPin = 9;
//const int wavePin  = 10;
//const int dancePin = 11;
const int dancePin = 12;
//const int  = 13; // system pin
//const int  = 14; // A0
//const int  = 15; // A1
//const int  = 16; // A2
//const int  = 17; // A3
//const int  = 18; // A4
//const int  = 19; // A5

int inCmd;  // to manage serial data
int wavestart = 115; 
int dancestart = 90; 

void setup() {
  // initialize serial communication:
  // Serial.begin(9600);   // USB wired
  Serial.begin(115200);    // bluetooth

  waver.attach(wavePin);
  waver.write(wavestart);

  dancer.attach(dancePin);
  dancer.write(dancestart);

  pinMode(buttonPin, INPUT); 

  // initialize the different pins:
  pinMode(blinkPin, OUTPUT);
  pinMode(goPin, OUTPUT);
  pinMode(heartPin3, OUTPUT);
  pinMode(heartPin4, OUTPUT);
  pinMode(heartPin5, OUTPUT);
}

void loop() {

  digitalWrite(goPin,HIGH); //ready to receive commands

  // see if there's incoming serial data:
  if (Serial.available() > 0) {
    // read the oldest byte in the serial buffer:
    inCmd = Serial.read();

    switch (inCmd) {
    case 'W':
      digitalWrite(goPin,LOW);
      waveServo();
      break;
    case 'L':
      digitalWrite(goPin,LOW);
      showLove();
      break;
    case 'D':
      digitalWrite(goPin,LOW);
      happyDance();
      break;
    case 'B':
      digitalWrite(goPin,LOW);
      ledBlinky();
      break;
    default:
      digitalWrite(goPin,HIGH);
    }
  }
  else {
    if (digitalRead(buttonPin) == 1) {
      digitalWrite(goPin,LOW);
      sendTweet();
      delay(2000);
    }
  }
}

void sendTweet() {
  // response to button push (or other tweet commands?) - sends random tweet from database
  Serial.println("T");
}

void waveServo() {
  // function to respond to "wave" command
  //digitalWrite(wavePin,HIGH); 
  int sweep = 10; 
  int right = wavestart+sweep;
  int left = wavestart-sweep;
  int pos;
  int waves = 5;

  for (int i = 0; i < waves; i++) {
    for(pos = left; pos < right; pos += 1) {
      waver.write(pos);              
      delay(15);        
    } 
    for(pos = right; pos > left; pos -= 1) {                                
      waver.write(pos);
      delay(15);
    } 
  }
  waver.write(wavestart);
  //digitalWrite(wavePin,LOW);
}

void showLove() { 
  // function for LED matrix beating heart (?) - response to "love" command
  int heartpins[] = { 
    heartPin3, heartPin4, heartPin5     };
  int numPins = sizeof(heartpins);
  int flashes = 3;

  for (int i=0; i < numPins; i++) {
    digitalWrite(i, HIGH);
    delay(750);
  }

  for (int i=0; i < flashes;i++) {
    for (int i=0; i < numPins; i++) {
      digitalWrite(i, LOW);
    }
    delay(500);
    for (int i=0; i < numPins; i++) {
      digitalWrite(i, HIGH);
    }
    delay(500);
  }

  for (int i=numPins; i >= 0; i--) {
    digitalWrite(i, LOW);
    delay(750);
  }
  /**** LED TESTING ONLY ********
  for (int i = 0; i <= 5; i++) {
    digitalWrite(heartPin3,HIGH);
    delay(100);
    digitalWrite(heartPin4,HIGH);
    delay(100);
    digitalWrite(heartPin5,HIGH);
    delay(100);
    digitalWrite(heartPin3,LOW);
    delay(100);
    digitalWrite(heartPin4,LOW);
    delay(100);
    digitalWrite(heartPin5,LOW);
    delay(100);
  }
  /******************************/
}

void happyDance() {
  // function for happy feet dance - response to "dance" command
  int sweep = 10; 
  int right = dancestart+sweep;
  int left = dancestart-sweep;
  int pos;
  int dances = 5;

  for (int i = 0; i < dances; i++) {
    for(pos = left; pos < right; pos += 1) {
      dancer.write(pos);              
      delay(25);        
    } 
    for(pos = right; pos > left; pos -= 1) {                                
      dancer.write(pos);              // tell servo to go to position in variable 'pos' 
      delay(25);                       // waits 15ms for the servo to reach the position 
    } 
  }
  dancer.write(dancestart);

  /**** LED TESTING ONLY ********
   * for (int i = 0; i <= 5; i++) {
   * digitalWrite(lfPin,HIGH);
   * digitalWrite(dancePin,LOW);
   * delay(100);
   * digitalWrite(dancePin,HIGH);
   * digitalWrite(lfPin,LOW);
   * delay(100);
   * }
   * digitalWrite(dancePin,LOW);
   * digitalWrite(lfPin,LOW);
  /******************************/
}

void ledBlinky() {
  // function to respond to "blink" command
  for (int i = 0; i < 5; i++) {
    digitalWrite(blinkPin, HIGH);   // set the LED on
    delay(1000);                     // wait for a second
    digitalWrite(blinkPin, LOW);    // set the LED off
    delay(1000);                     // wait for a second
  }
}

void setGo() {
  // possible function for testing and setting the "go" light.
  digitalWrite(goPin,LOW);
  delay(100);
}



