// Escape XML special markup chracters: tag delimiter < > and entity
// reference start delimiter &. The escaped string can be used in XML
// text portions (i.e. between tags).
function xmlEscapeText(s) {
  return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

// An implementation of the debug log. 
var logging__ = true;

function Log() {};

Log.lines = [];

Log.write = function(s) {
  if (logging__) {
    this.lines.push(xmlEscapeText(''+s));
    this.show();
  }
};

// Writes the given XML with every tag on a new line.
Log.writeXML = function(xml) {
  if (logging__) {
    var s0 = xml.replace(/</g, '\n<');
    var s1 = xmlEscapeText(s0);
    var s2 = s1.replace(/\s*\n(\s|\n)*/g, '<br/>');
    this.lines.push(s2);
    this.show();
  }
}

// Writes without any escaping
Log.writeRaw = function(s) {
  if (logging__) {
    this.lines.push(s);
    this.show();
  }
}

Log.clear = function() {
  if (logging__) {
    var l = this.div();
    l.innerHTML = '';
    this.lines = [];
  }
}

Log.show = function() {
  var l = this.div();
  l.innerHTML += this.lines.join('<br/>') + '<br/>';
  this.lines = [];
  l.scrollTop = l.scrollHeight;
}

Log.div = function() {
  var l = document.getElementById('log');
  if (!l) {
    l = document.createElement('div');
    l.id = 'log';
    l.style.position = 'absolute';
    l.style.right = '20px';
    l.style.bottom = '25px';
    l.style.width = '250px';
    l.style.height = '350px';
    l.style.overflow = 'auto';
    l.style.backgroundColor = '#f0f0f0';
    l.style.border = '1px solid gray';
    l.style.fontSize = '11px';
    l.style.padding = '5px';
    l.style.zIndex = '100';
    $('body').append(l);
  }
  return l;
}