
import processing.serial.*;
import processing.video.*;
import org.seltar.Bytes2Web.*; // Special thanks - http://libraries.seltar.org/postToWeb/

Capture cam;
Serial port; 

PFont tweetFont, statFont;

int m_id, numhash, chktime, twttime, cdtime, countDown, chkDelay, twtDelay, sCap;

int run = 1;

int dispw = 1024;
int disph = 740;

int camw = 800;
int camh = 600;

int camx, camy;

String datalink = "http://jann.ae/arthur/tweet/";
String picUrl = "http://jann.ae/arthur/cam/upload.php";
String twtname, twtnick, twttext, cmd, hash, success, cDown;

void setup() {
  size(dispw, disph);
  background(0);

  chktime = millis();
  twttime = millis();
  cdtime = millis();
  countDown = 1000;   // count down to check every second
  chkDelay = 12000;   // limit twitter pings to every 12 seconds
  twtDelay = 600000;  // tweet out of boredom every 10 minutes

  tweetFont = loadFont("CharcoalCY-24.vlw");
  statFont = loadFont("charcoalcy-12.vlw");

  println(Serial.list());
  //port = new Serial(this, Serial.list()[0], 115200); // USB wired
  port = new Serial(this, Serial.list()[4], 115200); // bluetooth
  port.bufferUntil('\n');

  String[] devices = Capture.list();
  println(devices);

  // Change devices[0] to the proper index for your camera.
  cam = new Capture(this, camw, camh, devices[0]);
  camx = (dispw-camw)/2;
  camy = (disph-camh-35);
}

void draw() {
  int active = 0;

  if (millis() > chktime + chkDelay) {
    println("Checking..." + millis());
    displayStatus("Checking...");
    findMentions();
    chktime = millis();
  }
  if (millis() > twttime + twtDelay) {
    println("Tweeting..." + millis());
    displayStatus("Tweeting...");
    sendTweet("T", 0);
    twttime = millis();
  }
  fill(0, 2);
  noStroke();
  rect(0, 0, width, height);

  while (port.available () > 0) {
    String cmd = port.readString();   
    if (cmd != "") {
      cmd = trim(cmd);
      println(cmd);

      int men;

      if (cmd.equals("T")) {
        men = 0;
        sendTweet(cmd, men);
        //println("sendTweet("+cmd+","+men+")");
      }
      if (cmd.equals("P")) {
        sCap = 1;
      }
      else {
        men = 1;
      }
    }
  }
  if (cam.available() == true) {
    cam.read();
    set(camx, camy, cam);
  }
  if (sCap == 1) {
    takePic();
    sCap = 0;
  }
}

void findMentions() { 
  String[] objlns = loadStrings(datalink + "?func=men&type=csv");

  for (int i = 0; i < objlns.length; i++) {
    String[] objs = split(objlns[0], "|");

    for (int j = 0; j < objs.length; j++) {
      print(objs[j]+",");
    }

    m_id = int(objs[0]);     // ref_id for mention
    //println("m_id: "+m_id);
    numhash = int(objs[1]);  // number of hashes in the tweet
    //println("numhash: "+numhash);
    int hashidx = 2;

    String[] hashes = new String[numhash];

    for (int h = 0; h < numhash; h++) {
      //println("objs["+hashidx+"]: "+objs[hashidx]);

      hashes[h] = objs[hashidx];
      //println("hashes["+h+"]: "+hashes[h]);
      hashidx++;
      if (hashes[h].equals("N")) {
        numhash = numhash-1;
      }
      else {
        hash = hashes[h];
        port.write(hashes[h]);
      }
    }

    success = objs[hashidx];
    port.write(success);
    hashidx++;

    twtname = objs[hashidx];
    hashidx++;
    twtnick = objs[hashidx];
    hashidx++;
    twttext = objs[hashidx];
    background(0);
    displayTweet(twtname, twtnick, twttext);

    if (numhash > 1) {
      sendTweet("M", 1);
      println("sendTweet(M,1)");
    }
    if (numhash == 1) {
      sendTweet(hash, 1);
      //println("sendTweet("+hash+",1)");
    }
  }
}

void sendTweet(String twtcmd, int twtmen) {
  String[] objlns = loadStrings(datalink + "?func=twt&cmd="+twtcmd+"&mid="+twtmen);
  if (objlns.length > 0) {
    println(objlns[0]);
  }
}

void displayTweet(String tname, String nick, String twtext) {
  textAlign(LEFT, TOP);
  textFont(tweetFont, 24);
  if (twtext != "") {
    int pad = 10;
    int textx = pad;
    int texty = pad;

    fill(245);
    text("@"+nick+" ("+tname+") says: ", textx, texty, width-pad*2, height-pad*2);
    stroke(150);
    texty = texty+pad*4;
    line(textx, texty, width-textx, texty);
    texty = texty+pad;

    text(twtext, textx, texty, width-pad, height-pad);
  }
}

void displayStatus(String action) {
  int remHits = 0;
  int remPics = 0;
  int pad = 10;
  int textx = pad;
  int texty = height-pad*2;
  textAlign(LEFT, TOP);
  textFont(statFont, 12);

  String[] objlns = loadStrings(datalink + "?func=lim&type=csv");

  for (int i = 0; i < objlns.length; i++) {
    String[] objs = split(objlns[0], "|");

    remHits = int(objs[0]);
    remPics = int(objs[1]);
  }
  //println(remHits);
  fill(0);
  noStroke();
  rect(0, texty-pad, width, texty);
  stroke(150);
  line(0, texty-pad, width, texty-pad);
  smooth();
  fill(245);
  text(remHits+" remaining hits", textx, texty);
  if (action != "none") {
    text(action, width-100, texty, width-pad, height-pad);
  }
}

void takePic() {
  ImageToWeb img = new ImageToWeb(this);
  img.save("jpg", true);
  img.post("_img", picUrl, "cam", false, img.getBytes(g));
}
