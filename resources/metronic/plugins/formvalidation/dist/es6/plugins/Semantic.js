import e from"../utils/classSet";import s from"../utils/hasClass";import t from"./Framework";export default class n extends t{constructor(e){super(Object.assign({},{formClass:"fv-plugins-semantic",messageClass:"ui pointing red label",rowInvalidClass:"error",rowPattern:/^.*(field|column).*$/,rowSelector:".fields",rowValidClass:"fv-has-success"},e));this.messagePlacedHandler=this.onMessagePlaced.bind(this)}install(){super.install();this.core.on("plugins.message.placed",this.messagePlacedHandler)}uninstall(){super.uninstall();this.core.off("plugins.message.placed",this.messagePlacedHandler)}onIconPlaced(s){const t=s.element.getAttribute("type");if("checkbox"===t||"radio"===t){const t=s.element.parentElement;e(s.iconElement,{"fv-plugins-icon-check":true});t.parentElement.insertBefore(s.iconElement,t.nextSibling)}}onMessagePlaced(e){const t=e.element.getAttribute("type");const n=e.elements.length;if(("checkbox"===t||"radio"===t)&&n>1){const l=e.elements[n-1];const a=l.parentElement;if(s(a,t)&&s(a,"ui")){a.parentElement.insertBefore(e.messageElement,a.nextSibling)}}}}