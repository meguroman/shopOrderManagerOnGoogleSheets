function myFunction() {

  //get unread threads from inbox of Gmail
  var threads = GmailApp.search("label:inbox is:unread [BUYMA]注文");

  //make var
  var msg;
  var date;
  var subject;
  var body;
  var Id;
  var matchItemName;
  var matchItemUrl;
  var countDataNum = 0;
  var soldItemData = new Array()
  
  for ( var n in threads){
    var thread = threads[n];
    var msgs = thread.getMessages();
    
    for ( var m in msgs){
      msg = msgs[m];
      
      if(msg.isUnread()) {
        date = msg.getDate();
        subject = msg.getSubject();
        body = msg.getBody();
        Id = msg.getId();
        
        Logger.log("\n ****************************************************************\n"
                   + "*******threadNo." + n + "*******\n"
                   + "*******msgNo." + m + "*******\n"
                   + "*******" + date + "*******\n"
                   + "*******" + date + "*******\n"
                   + "*******" + Id + "*******\n"
                   + "*******" +subject + "********\n"
                   + "****************************************************************\n");
        
        
        //choose only order mail from buyma by subject.
        if(subject.indexOf("[BUYMA]注文") != -1) {
          
          //2次元配列の2次元目を追加
          soldItemData[countDataNum] = new Array();
          
          //format sold date.
          var formatDate = date.getMonth()+1 + "月" + date.getDate() + "日";
          
          //get sold Item Name & sold page URL.
          matchItemName = body.match(/・商品：(.*?)[\s\(]/);
          matchItemUrl = body.match(/(http.*buyerorderdetail.*)</);
          matchItemDate = body.match(/・.*日時：(.*?)</);
          Logger.log("\n ****商品名は「" + matchItemName[1] + "」です。****\n");
          Logger.log("\n ****商品URLは「" + matchItemUrl[1] + "」です。****\n");
          Logger.log("\n ****商品注文日は「" + matchItemDate[1] + "」です。****\n");
          soldItemData[countDataNum][0] = matchItemName[1];
          soldItemData[countDataNum][1] = matchItemUrl[1];
          soldItemData[countDataNum][2] = matchItemDate[1];
          
          countDataNum++;
        }
        msg.markRead();
      }
    }
  }
  
  makePostData(soldItemData);
  
}

function makePostData(arrayData) {

  var payload = {}
  var i = 0;
  
  
  //注文情報データ配列を注文日時が古い順でソート
  arrayData.sort(function(a, b){
    return a[2] > b[2] ? 1 : -1;
  });
  
  
  //POST送信用データ配列(payload)の準備
  while(i < arrayData.length) {
    
    payload["data[" + i + "][name]"] = arrayData[i][0];
    payload["data[" + i + "][url]"] = arrayData[i][1];
    Logger.log("arrayData[" + i + "][0] ==>" + arrayData[i][0] + "\n");
    Logger.log("arrayData[" + i + "][1] ==>" + arrayData[i][1] + "\n");
    
    i++;
  }
  
  Logger.log(" ");
  Logger.log(" ");
  Logger.log("payload in makePostData ==>>");
  Logger.log(payload);
  
  sendHttpPost(payload);
}

function sendHttpPost(payload) {
  
  var options =
      {
        "method" : "post",
        "payload" : payload
      };
  
  Logger.log("options in sendHttpPost ===>" + options);
  
  var response = UrlFetchApp.fetch("http://navoglio.xsrv.jp/scripts/BuymaManager/writeOrderToSS.php", options);

  Logger.log(" ");
  Logger.log(" ");
  Logger.log("response in sendHttpPost ===>" + response);
  
}



