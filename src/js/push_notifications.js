// Simple browser push notification utility
function notifyUser(title,body){
  if(Notification.permission==='granted'){
    new Notification(title,{body});
  }else if(Notification.permission!=='denied'){
    Notification.requestPermission().then(p=>{
      if(p==='granted') new Notification(title,{body});
    });
  }
}
// Example: notifyUser('New Chat Message','You have a new message in APS Dream Homes!');
