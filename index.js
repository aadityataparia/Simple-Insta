var user = [],
  old = [];
loadStyle("https://fonts.googleapis.com/css?family=Raleway:400,700|Source+Sans+Pro:400,700");
var MAIN = document.body || document.getElementsByTagName("body")[0];
var LOADER = document.querySelector(".loader");
var PROGRESS = document.querySelector(".progress");
var CLICK_EVENTS = {};
var SUPPORT_PASSIVE = false,
  magiclink = false;
var lf_get = localforage.createInstance({
  name: "APIget"
});
try {
  var opts = Object.defineProperty({}, 'passive', {
    get: function() {
      SUPPORT_PASSIVE = true;
    }
  });
  window.addEventListener("test", null, opts);
} catch (e) {}

if (SUPPORT_PASSIVE) {
  var ELopt = {
    passive: true,
    capture: false
  };
} else {
  var ELopt = false;
}
//Cross browser requestAnimationFrame
(function() {
  var lastTime = 0;
  var vendors = ['webkit', 'moz'];
  for (var x = 0; x < vendors.length && !window.requestAnimationFrame; ++x) {
    window.requestAnimationFrame = window[vendors[x] + 'RequestAnimationFrame'];
    window.cancelAnimationFrame =
      window[vendors[x] + 'CancelAnimationFrame'] || window[vendors[x] + 'CancelRequestAnimationFrame'];
  }

  if (!window.requestAnimationFrame)
    window.requestAnimationFrame = function(callback, element) {
      var currTime = new Date().getTime();
      var timeToCall = Math.max(0, 16 - (currTime - lastTime));
      var id = window.setTimeout(function() {
          callback(currTime + timeToCall);
        },
        timeToCall);
      lastTime = currTime + timeToCall;
      return id;
    };

  if (!window.cancelAnimationFrame)
    window.cancelAnimationFrame = function(id) {
      clearTimeout(id);
    };
}());

//cross browser matches
if (!Element.prototype.matches) {
  Element.prototype.matches =
    Element.prototype.matchesSelector ||
    Element.prototype.mozMatchesSelector ||
    Element.prototype.msMatchesSelector ||
    Element.prototype.oMatchesSelector ||
    Element.prototype.webkitMatchesSelector ||
    function(s) {
      var matches = (this.document || this.ownerDocument).querySelectorAll(s),
        i = matches.length;
      while (--i >= 0 && matches.item(i) !== this) {}
      return i > -1;
    };
}

//cross browser forEach
var forEach = function(array, callback) {
  if (typeof array == 'object' && array != null && array) {
    for (var key in array) {
      if (array.hasOwnProperty(key) && array[key] && key != "length") {
        callback.call(array[i], array[key], key); // passes back stuff we need
      }
    }
  } else {
    if (array.length < 1) {
      return false;
    }
    for (var i = 0; i < array.length; i++) {
      callback.call(array[i], array[i], i); // passes back stuff we need
    }
  }
};

//cross browser indexOf
Array.prototype.indexOf || (Array.prototype.indexOf = function(d, e) {
  var a;
  if (null == this) throw new TypeError('"this" is null or not defined');
  var c = Object(this),
    b = c.length >>> 0;
  if (0 === b) return -1;
  a = +e || 0;
  Infinity === Math.abs(a) && (a = 0);
  if (a >= b) return -1;
  for (a = Math.max(0 <= a ? a : b - Math.abs(a), 0); a < b;) {
    if (a in c && c[a] === d) return a;
    a++
  }
  return -1
});
//replaceAll
String.prototype.replaceAll = function(search, replacement) {
  var target = this;
  return target.replace(new RegExp(search, 'g'), replacement);
};
//add remove function to array
Array.prototype.remove = function() {
  var what, a = arguments,
    L = a.length,
    ax;
  while (L && this.length) {
    what = a[--L];
    while ((ax = this.indexOf(what)) !== -1) {
      this.splice(ax, 1);
    }
  }
  return this;
};
//addclass, removeClass
function addClass(elem, classN) {
  if (typeof elem == "string") {
    elem = document.querySelector(elem);
  }
  if (!elem) {
    return false;
  }
  if (elem.className.length < 1) {
    elem.className = classN;
  }
  var classes = elem.className.split(" ");
  if (classes.indexOf(classN) < 0) {
    classes.push(classN);
  }
  elem.className = classes.join(" ");
}

function removeClass(elem, classN) {
  if (typeof elem == "string") {
    elem = document.querySelector(elem);
  }
  if (!elem) {
    return false;
  }
  var classes = elem.className.split(" ");
  classes.remove(classN);
  elem.className = classes.join(" ");
}

function siblings(elem, classN) {
  var r = [];
  var childs = children(elem.parentElement, '*');
  forEach(childs, function(child) {
    if (child.matches(classN)) {
      r.push(child);
    }
  });
  return r;
}

function parent(x, k) {
  while (x) {
    if (x.matches(k)) {
      return x;
    }
    x = x.parentElement;
  }
  return false;
}

function children(elem, classN) {
  var c = elem.children;
  var r = [];
  if (!c) {
    return false;
  }
  for (var i = 0; i < c.length; i++) {
    if (c[i].matches(classN)) {
      r.push(c[i]);
    }
  }
  return r;
}

//loading
var loader = {
  on: function() {
    LOADER.style.display = "block";
    addClass(MAIN, "loading");
  },
  off: function() {
    LOADER.style.display = "none";
    removeClass(MAIN, "loading");
  }
}

//progress
var progress = {
  on: function() {
    PROGRESS.style.display = "block";
    addClass(MAIN, "progress");
  },
  off: function() {
    PROGRESS.style.display = "none";
    removeClass(MAIN, "progress");
  },
  set: function(num) {
    if (num > 99) {
      setTimeout(function() {
        PROGRESS.style.display = "none";
      }, 300);
    } else {
      PROGRESS.style.display = "block";
    }
    PROGRESS.querySelector(".percent").style.width = num + '%';
  },
  set num(num) {
    if (num > 99) {
      setTimeout(function() {
        PROGRESS.style.display = "none";
      }, 300);
    } else {
      PROGRESS.style.display = "block";
    }
    PROGRESS.querySelector(".percent").style.width = num + '%';
  }
}

//notification
var nottime, note = document.getElementById("notification");

function notify(msg, classN) {
  classN = classN == undefined ? "" : classN;
  if (nottime) {
    clearTimeout(nottime);
  }
  note.className = classN;
  note.style.display = 'block';
  note.innerHTML = msg;
  note.style.opacity = "1";
  nottime = setTimeout(function() {
    note.style.opacity = "0";
    setTimeout(function() {
      note.style.display = 'none';
      removeClass(note, classN);
    }, 300);
  }, 3000);
}

//XMLHttpRequest wrapper
var API = {
  getHTTP: function(url, params, callback, failure, type, formdata) {
    progress.num = 15;
    var ans = Object.keys(params).map(function(k) {
        return encodeURIComponent(k) + '=' + encodeURIComponent(params[k])
      }).join('&'),
      noload = false;
    if (type == "GET") {
      lf_get.getItem(url + '?' + ans).then(function(sp) {
        if (typeof callback == "function" && sp && sp.ok) {
          callback(sp);
          lfres = sp;
          noload = true;
          progress.num = 100;
        }
      });
    }
    var http = new XMLHttpRequest(),
      lfres;
    loader.off();
    http.onload = function() {
      progress.set(100);
      if (http.status > 199 && http.status < 400) {
        try {
          var result = JSON.parse(http.responseText);
        } catch(err) {
          notify("Error while executing request, try again later.", "red");
        }
        if (result.status == "OK" || result.ok) {} else if (result.status == 401 && !user.id) {
          showAddPC(document.querySelector('[data-show="userlogin"]'));
          notify("Login and try again.", "red");
        } else {
          notify(result.message || result.status || "Unknown error occurred", "red");
          if (typeof failure == "function") {
            failure(result.status);
          }
        }
        lf_get.setItem(url + '?' + ans, result);
        if (typeof callback == "function") {
          callback(result);
        }
        return result;
      } else {
        if (typeof failure == "function") {
          failure(http.status);
          notify("Error while executing request, try again later.", "red");
        }
        return false;
      }
    };
    http.onerror = function(e) {
      progress.set(100);
      if (!noload) {
        notify("Network Error, try again.", "red");
      }
    };
    http.ontimeout = function(e) {
      progress.set(100);
      if (!noload) {
        notify('Request timed out', "red");
      }
    }
    http.onprogress = function(e) {
      if (e.lengthComputable && !noload) {
        var percent = 15 + (e.loaded / e.total) * 85;
        progress.set(percent);
      }
    }
    params = typeof params == 'undefined' ? {} : params;
    localforage.getItem("sessionpass").then(function(sp) {
      http.open(type, url + '?' + ans, true);
      if (sp) {
        http.setRequestHeader("x-session-pass", sp);
      }
      http.setRequestHeader("accept", "application/json");
      if (formdata) {
        // http.setRequestHeader("Content-type", "multipart/form-data");
        http.send(formdata);
      } else {
        // http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        http.send();
      }
    });
  },
  get: function(url, params, callback, failure, form) {
    return API.getHTTP(url, params, callback, failure, "GET", form);
  },
  post: function(url, params, callback, failure, form) {
    return API.getHTTP(url, params, callback, failure, "POST", form);
  },
  put: function(url, params, callback, failure, form) {
    return API.getHTTP(url, params, callback, failure, "PUT", form);
  },
  delete: function(url, params, callback, failure, form) {
    return API.getHTTP(url, params, callback, failure, "DELETE", form);
  },
  getHTML: function(url, params, callback, failure) {

  }
}

//Assign polyfill
if (typeof Object.assign != 'function') {
  Object.assign = function(target) {
    'use strict';
    if (target == null) {
      throw new TypeError('Cannot convert undefined or null to object');
    }

    target = Object(target);
    for (var index = 1; index < arguments.length; index++) {
      var source = arguments[index];
      if (source != null) {
        for (var key in source) {
          if (Object.prototype.hasOwnProperty.call(source, key)) {
            target[key] = source[key];
          }
        }
      }
    }
    return target;
  };
}

//load style async
function loadStyle(url, callback) {
  var style = document.createElement("link")
  style.rel = "stylesheet";
  if (style.readyState) {
    style.onreadystatechange = function() {
      if (style.readyState == "loaded" ||
        style.readyState == "complete") {
        style.onreadystatechange = null;
        if (typeof callback == "function") {
          callback();
        }
      }
    };
  } else {
    style.onload = function() {
      if (typeof callback == "function") {
        callback();
      }
    };
  }
  style.href = url;
  document.body.appendChild(style);
}

//Click event listner
function clickHandler(e) {
  e = e || window.event;
  var target;
  target = e.target || e.srcElement;
  for (var k in CLICK_EVENTS) {
    x = target;
    while (x) {
      if (x.matches(k)) {
        var fn = window[CLICK_EVENTS[k]];
        if (typeof fn === "function") {
          fn(x, e);
        }
      }
      if (x) {
        x = x.parentElement;
      }
    }
  }
}

if (MAIN.addEventListener) {
  MAIN.addEventListener('click', clickHandler, false);
} else {
  MAIN.attachEvent('onclick', clickHandler);
}

//focus input when clicked on label
CLICK_EVENTS["label"] = "labelClick";

function labelClick(elem) {
  var i = children(elem.parentElement, 'input')[0] || children(elem.parentElement, 'textarea')[0];
  i.focus();
}
//input
window.addEventListener("change", function(e) {
  winChange(e.target)
});
function winChange(target) {
  if (target.matches("input") || target.matches("textarea")) {
    target.setAttribute('value', target.value);
  }
}
//data binding
var bR = {
  html: function(selector, value, container) {
    if (!container) {
      container = "";
    } else {
      container += " ";
    }
    if (!old[selector + container + "html"]) {
      old[selector + container + "html"] = "";
    }
    if ((value != undefined && old[selector + container + "html"] != value) || value == null) {
      old[selector + container + "html"] = value;
      forEach(document.querySelectorAll(container + "*[db-value='" + selector + "']:not(input)"), function(elem) {
        elem.innerHTML = value;
      });
    }
  },
  input: function(selector, value, container) {
    if (!container) {
      container = "";
    } else {
      container += " ";
    }
    if (!old[selector + container + "input"]) {
      old[selector + container + "input"] = "";
    }
    if ((value != undefined && old[selector + container + "input"] != value) || value == null) {
      old[selector + container + "input"] = value;
      forEach(document.querySelectorAll(container + "*[db-value='" + selector + "']"), function(elem) {
        elem.setAttribute("value", value);
        elem.value = value;
      });
    }
  },
  photosFeed: function(selector, value, container) {
    var html = "";
    if (!container) {
      container = "";
    } else {
      container += " ";
    }
    if (!old[selector + container + "input"]) {
      old[selector + container + "input"] = "";
    }
    if ((value != undefined && old[selector + container + "input"] != value) || value == null) {
      old[selector + container + "input"] = value;
      forEach(document.querySelectorAll(container + "*[db-value='" + selector + "']"), function(elem) {
        elem.innerHTML = html;
      });
    }
  }
}

function signinuser(data) {
  user = data.results[0];
  var sessionpass = data.sessionpass;
  var datatoinput = {
      "sessionpass": sessionpass,
      "user": user
    },
    yes;
  Object.assign(Buser, user);
  loggedin(true);
  hideAddPC("#userlogin");
  if (magiclink) {
    addClass("#setpass", "show");
  }
  localforage.getItem('sessions').then(function(value) {
    logged = false;
    if (value != null) {
      forEach(value, function(session, index) {
        if (session.user.email == user.email) {
          yes = index;
          logged = true;
          value[index].sessionpass = data.sessionpass;
          value[index].user = user;
        }
      });
      if (!logged) {
        var index = value.push(datatoinput) - 1;
        localforage.setItem('sessions', value);
        localforage.setItem("currentuser", index);
        localforage.setItem("sessionpass", sessionpass);
      } else {
        localforage.setItem('sessions', value);
        localforage.setItem("currentuser", yes);
        localforage.setItem("sessionpass", sessionpass);
      }
    } else {
      var value = [];
      value.push(datatoinput);
      localforage.setItem('sessions', value);
      localforage.setItem("currentuser", 0);
      localforage.setItem("sessionpass", sessionpass);
    }
  }).catch(function(err) {
    console.log(err);
  });
}

window.onload = function() {
  loader.off();
}
localforage.getItem("currentuser").then(function(c) {
  if (c !== null) {
    localforage.getItem("sessions").then(function(sessions) {
      signinuser({
        sessionpass: sessions[c].sessionpass,
        results: [sessions[c].user]
      });
      API.get("./api/v1/api.php", {
        url: "user"
      }, function(res) {
        if (res.status === 200) {
          signinuser(res)
        } else {
          logout();
        }
      });
    });
  } else {
    logout();
  }
}).catch(function(err) {
  console.log(err);
});
if (window.location.pathname[0] != '/') {
  var pathname = '/' + window.location.pathname;
} else {
  var pathname = window.location.pathname;
}
var path = pathname.split("/");

function getRequests() {
  var s1 = location.search.substring(1, location.search.length).split('&'),
    r = {},
    s2, i;
  for (i = 0; i < s1.length; i += 1) {
    s2 = s1[i].split('=');
    r[decodeURIComponent(s2[0]).toLowerCase()] = decodeURIComponent(s2[1]);
  }
  return r;
};
var QS = getRequests();
if (QS['i'] > 0) {
  path[2] = QS['i'];
}
var qs = (function(a) {
  if (a == "") return {};
  var b = {};
  for (var i = 0; i < a.length; ++i) {
    var p = a[i].split('=', 2);
    if (p.length == 1)
      b[p[0]] = "";
    else
      b[p[0]] = decodeURIComponent(p[1].replace(/\+/g, " "));
  }
  return b;
})(window.location.search.substr(1).split('&'));

//loading user
function loggedin(nocheck) {
  logged = true;
  forEach(document.querySelectorAll(".logged"), function(elem) {
    elem.style.display = 'block';
  });
  forEach(document.querySelectorAll(".not-logged"), function(elem) {
    elem.style.display = 'none';
  });
  if (!nocheck || nocheck == undefined) {
    localforage.getItem("sessionpass").then(function(val) {
      if (val === null) {
        logout();
      }
    });
  }
}

//email login
function passLogin(email, pass) {
  API.post("./api/v1/auth/index.php", {
    url: 'signup',
    type: "password",
    what: "signin",
    password: pass,
    email: email
  }, function(res) {
    if (res.status == 200) {
      signinuser(res);
      magiclink = false;
    }
  });
}

//logout
function logout() {
  logged = false;
  localforage.removeItem('sessions');
  localforage.removeItem('sessionpass');
  localforage.removeItem('currentuser');
  forEach(document.querySelectorAll(".logged"), function(elem) {
    elem.style.display = 'none';
  });
  forEach(document.querySelectorAll(".not-logged"), function(elem) {
    elem.style.display = '';
  });
}

//fixedinput
var finput = document.querySelectorAll(".fixedinput");
forEach(finput, function(fi) {
  fi.querySelector(".close").addEventListener("click", function() {
    removeClass(fi, "show");
    var hashl = window.location.hash.substr(1);
    if (fi.getAttribute("id") == hashl) {
      history.back();
    }
  });
  fi.querySelector(".bkg").addEventListener("click", function() {
    removeClass(fi, "show");
    var hashl = window.location.hash.substr(1);
    if (fi.getAttribute("id") == hashl) {
      history.back();
    }
  });
});

function showAddPC(elem) {
  if (typeof elem == 'string') {
    elem = document.querySelector(elem);
  }
  var id = elem.getAttribute("data-show");
  addClass("#" + id, "show");
  history.pushState({
    fixedinput: id
  }, null, "#" + id);
}

function hideAddPC(fi) {
  if (typeof fi == 'string') {
    fi = document.querySelector(fi);
  }
  removeClass(fi, "show");
  var hashl = window.location.hash.substr(1);
  if (fi && fi.getAttribute("id") == hashl) {
    history.back();
  }
}

CLICK_EVENTS[".showfi"] = "showAddPC";

//magic link
function emailLogin(email) {
  if (!validateEmail(email)) {
    notify("Enter a valid Email Address", "red");
    return false;
  }
  notify("Sending magic link to " + email + ".");
  addClass(MAIN, "verify");
  // email.substr(id.length - 9);
  API.post("./api/v1/auth/index.php", {
    url: "magiclink",
    email: email
  }, function(res) {
    removeClass(MAIN, "loginfs");
    if (res.status == 200 && res.emailsent) {
      notify("Magic Code sent to " + email + ". Do check Spam folder if it is not in your Inbox.");
      addClass(MAIN, "verify");
      Buser.email = email;
      user.email = email;
    } else {
      notify("Error while sending Magic code! Try again after some time.", "red");
      removeClass(MAIN, "verify");
    }
  })
}

function verifyEmail(code, email) {
  API.get("./api/v1/auth/index.php", {
    url: "verify",
    code: code,
    email: email,
    type: 'password'
  }, function(res) {
    if (res.status == 200) {
      if (res['verify_message'] == "OK") {
        magiclink = true;
        notify("Magic link verified! Signing In.", "green");
        signinuser(res);
        removeClass(MAIN, "verify");
      } else {
        notify(res['verify_message'], "red");
      }
    }
  });
}

function validateEmail(email) {
  var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@(([A-Za-z]+)\.)+([A-Za-z][A-Za-z]+)$/;
  return re.test(email);
}

//user Binding
var Buser = {
  set email(value) {
    bR.html("user.email", value);
    bR.input("user.email", value);
  },
  set id(value) {
    bR.html("user.pic", "<img class='fit' src='./api/v1/uploads/dps/" + value + ".jpg'>");
  }
};

//feed Binding
var Bfeed = {
  set photos(value) {
    var html = "", comments = "", deleteuser="";
    forEach(value, function(photo, key){
      if(photo.comments){
        forEach(photo.comments, function(comment){
          if(comment.userid == user.id){
            deleteuser = '<span class="right deletec" data-commentid="'+comment.id+'">X</span>';
          } else {
            deleteuser = "";
          }
          comments += '<p class="comment">' +
            '<span>'+comment.email+'</span>' + comment.comment + deleteuser +
          '</p>';
        });
      }
      if(photo.userid == user.id){
        deleteuser = '<div class="button s-4 m-3 red" onclick="deletePhoto('+photo.id+',event.target)">' +
          '<span>Delete</button>' +
        '</div>';
      } else {
        deleteuser = "";
      }
      html += '<div class="photo grid" data-photoid="'+photo.id+'">' +
        '<div class="about s-12">' +
          '<div class="username s-8 m-9">'+photo.email+'</div>' +
           deleteuser +
        '</div>' +
        '<a href="./photo?id='+photo.id+'"><img class="fit" src="./api/v1/uploads/photos/'+photo.id+'.jpg" title="'+photo.email+'\'s photo" alt="'+photo.email+'\'s photo">' +
        '<p class="acomment">Comment on photo</p></a>' +
        '<div class="comments">' +
          comments +
        '</div>' +
        '<form class="input" onsubmit="commentP('+photo.id+',this.comment.value,event.target); return false;">' +
          '<input type="text" name="comment" value="" required autocomplete="off">' +
          '<label for="comment">Comment</label>' +
          '<p>Press enter to comment.</p>' +
        '</form>' +
      '</div>';
    });
    bR.html("feed",html);
  }
}

//upload photo
document.querySelector("#uploadpic input").addEventListener("change", function() {
  var file = this.files[0];
  if (file.size > 10 * 1024 * 1024) {
    notify("Upload image smaller than 10MB", "red");
    return false;
  }
  var formData = new FormData();
  formData.append("photoimg", file);
  notify("Uploading image", "green");
  API.post("./api/v1/api.php", {
    url: "photo"
  }, function(res) {
    if (res.status == 200) {
      notify("Image uploaded successfully", "green");
      window.location = "./photo?id="+res.results[0].id;
    } else if (res.status == 1000) {
      notify(res.customerror, "red");
    } else {
      notify(res.message, "red");
    }
  }, function(res) {}, formData);
});

//upload dp
if(document.querySelector("#uploaddp input")){
  document.querySelector("#uploaddp input").addEventListener("change", function() {
    var file = this.files[0];
    if (file.size > 10 * 1024 * 1024) {
      notify("Upload image smaller than 10MB", "red");
      return false;
    }
    var formData = new FormData();
    formData.append("photoimg", file);
    notify("Uploading image", "green");
    API.post("./api/v1/api.php", {
      url: "dp"
    }, function(res) {
      if (res.status == 200) {
        notify("Image uploaded successfully", "green");
      } else if (res.status == 1000) {
        notify(res.customerror, "red");
      } else {
        notify(res.message, "red");
      }
    }, function(res) {}, formData);
  });
}

//change password
function changePass(newpass){
  API.post("./api/v1/auth/index.php", {
    url: "signup",
    type: "password",
    what: "signup",
    password: newpass,
    email: user.email
  }, function(res) {
    if (res.status == 200) {
      notify("New Password set successfully.", "green");
      user.password = 60;
      hideAddPC('#setpass');
    }
  });
}

//comment
function commentP(photoid, comment, target){
  API.post("./api/v1/api.php", {
    url: "comment",
    comment: comment,
    photoid: photoid
  }, function(res) {
    if (res.status == 200) {
      target.parentElement.querySelector(".comments").innerHTML += '<p class="comment"><span>'+user.email+'</span>'+comment+ '<span class="right deletec" data-commentid="'+res.inserted_id+'">X</span>' +'</p>';
      target.parentElement.querySelector(".input input").value = "";
    }
  });
}

//deleting photo
function deletePhoto(photoid, target){
  API.delete("./api/v1/api.php", {
    url: "photo",
    photoid: photoid
  }, function(res) {
    if (res.status == 200) {
      addClass(target.parentElement.parentElement.parentElement,"deleted")
      notify("Photo successfully deleted");
    }
  });
}

//different feed for different url
if (path[path.length-1].toLowerCase() == 'myphotos') {
  API.get("./api/v1/api.php", {
    url: "myphotos",
    from: 0
  }, function(res) {
    if (res.status == 200) {
      user.id = res.userid;
      Bfeed.photos = res.results;
    }
  });
} else if(path[path.length-1].toLowerCase() == 'photo'){
  var photoid = qs.id;
  API.get("./api/v1/api.php", {
    url: "photo",
    photoid: photoid
  }, function(res) {
    if (res.status == 200) {
      user.id = res.userid;
      Bfeed.photos = res.results;
    }
  });
} else {
  addClass(MAIN,"home");
  API.get("./api/v1/api.php", {
    url: "feed",
    from: 0
  }, function(res) {
    if (res.status == 200) {
      user.id = res.userid;
      Bfeed.photos = res.results;
    }
  });
}

//delete comment
CLICK_EVENTS['.deletec'] = "deleteComment";
function deleteComment(elem){
  var commentid = elem.getAttribute("data-commentid");
  API.delete("./api/v1/api.php", {
    url: "comment",
    commentid: commentid
  }, function(res) {
    if (res.status == 200) {
      addClass(elem.parentElement,"deleted")
      notify("Comment successfully deleted");
    }
  });
}

// add service worker
if ('serviceWorker' in navigator) {
  window.addEventListener('load', function() {
    navigator.serviceWorker.register('/sw.js');
  });
}