(()=>{"use strict";var e={211:e=>{e.exports=import("https://static.paynow.pl/scripts/PyG5QjFDUI.min.js")}},t={};function n(r){var a=t[r];if(void 0!==a)return a.exports;var o=t[r]={exports:{}};return e[r](o,o.exports,n),o.exports}(()=>{const e=window.React,{decodeEntities:t}=wp.htmlEntities,{getSetting:r}=wc.wcSettings,{registerPaymentMethod:a}=wc.wcBlocksRegistry,{useEffect:o}=wp.element,s=r("pay_by_paynow_pl_card_data",{}),l=t(s.title||"Card"),c=s.iconurl,i=t(s.available||!1),p=t(s.fields||"");let m=null;try{Promise.resolve().then(n.bind(n,211)).then((e=>e.load())).then((e=>e.get())).then((e=>{m=e.visitorId}))}catch(e){console.error("Cannot get fingerprint")}const y=t=>{const{eventRegistration:n,emitResponse:r}=t,{onPaymentProcessing:a}=n;return o((()=>{const e=a((async()=>{const e=document.querySelector('input[name="paymentMethodToken"]:checked'),t=e?e.value:null,n={};return t&&(n.paymentMethodToken=t),n.paymentMethodFingerprint=m,{type:r.responseTypes.SUCCESS,meta:{paymentMethodData:n}}}));return()=>{e()}}),[r.responseTypes.ERROR,r.responseTypes.SUCCESS,a]),(0,e.createElement)("div",{dangerouslySetInnerHTML:{__html:p}})};a({name:"pay_by_paynow_pl_card",label:(0,e.createElement)((t=>{const{PaymentMethodLabel:n}=t.components,r=(0,e.createElement)("img",{src:c,alt:l,name:l});return(0,e.createElement)(n,{className:"paynow-block-label",text:l,icon:r})}),null),content:(0,e.createElement)(y,null),edit:(0,e.createElement)(y,null),canMakePayment:()=>i,ariaLabel:l})})()})();