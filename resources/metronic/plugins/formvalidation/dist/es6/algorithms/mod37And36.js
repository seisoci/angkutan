export default function t(t,e="0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"){const n=t.length;const o=e.length;let l=Math.floor(o/2);for(let r=0;r<n;r++){l=((l||o)*2%(o+1)+e.indexOf(t.charAt(r)))%o}return l===1}