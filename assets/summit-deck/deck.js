(function(){
'use strict';
if (!window.BV_DECK || !window.React || !window.ReactDOM || !window.Motion || !window.htm) return;
const { useState, useEffect, useRef, useMemo, useCallback } = React;
const { motion, AnimatePresence, useScroll, useSpring, useTransform, useReducedMotion, useInView } = Motion;
const html = htm.bind(React.createElement);
const M = motion;
const ease = [0.22,1,0.36,1];

/* ===================== DATA — window.BV_DECK (ACF-driven; see inc/summit-deck/deck.php) ===================== */
const D = window.BV_DECK;
const IMG = D.img;
const FORM = D.form;
const TICKETS = D.summitPage;
const TIERS = D.tiers.map(t => ({ id:t.id, name:t.name, price:t.price, priceMax:t.priceMax, status:t.status, tag:t.tagline, intro:t.intro, bens:t.benefits, remain:t.remaining, slotsTotal:t.slotsTotal, badge:t.badge, feature:t.feature, m:Object.assign({tix:t.ticketsIncluded||0}, Object.fromEntries((t.compare||[]).map(k=>[k,1]))) }));
const COLS = [['tix','Tickets'],['led','LED board'],['shirt','T-shirt'],['ban','Stage banners'],['evb','Event banner'],['land','Landing page'],['spon','Sponsor page'],['swag','Swag bag'],['bag','Branded bag'],['mail','Emails'],['soc','Social'],['spaces','X Spaces'],['telehash','Telehash'],['thanks','Site thank-you']];

const fmt = n => '$' + n.toLocaleString('en-US');
const status = t => t.status==='sold' ? ['sold','Purchased'] : t.status==='low' ? ['low', t.remain ? t.remain+' remain' : 'Few left'] : ['open','Open'];
const WORDS = ['One','Two','Three','Four','Five','Six','Seven','Eight','Nine','Ten','Eleven','Twelve'];
const numberWord = n => (n>=1 && n<=12) ? WORDS[n-1] : String(n);
const rich = s => String(s||'').split(/\*\*(.+?)\*\*/).map((p,i)=> i%2 ? html`<strong key=${i}>${p}</strong>` : p);

/* ===================== helpers ===================== */
function Reveal({children, delay=0, y=26, className, style, once=true}){
  const reduce = useReducedMotion();
  return html`<${M.div} className=${className} style=${style}
    initial=${reduce?false:{opacity:.3, y}} whileInView=${{opacity:1, y:0}} viewport=${{once, amount:.3}}
    transition=${{duration:.7, ease, delay}}>${children}<//>`;
}
function useCountUp(target, active, ms=900){
  const [v,setV]=useState(0); const reduce=useReducedMotion();
  useEffect(()=>{ if(!active) return; if(reduce){setV(target);return;} let raf,t0; const step=(t)=>{ if(!t0)t0=t; const p=Math.min(1,(t-t0)/ms); setV(Math.round(target*(1-Math.pow(1-p,3)))); if(p<1) raf=requestAnimationFrame(step); }; raf=requestAnimationFrame(step); return ()=>cancelAnimationFrame(raf); },[target,active,reduce,ms]);
  return v;
}
function useToast(){
  const [msg,setMsg]=useState('');
  const show=(m)=>{ setMsg(m); setTimeout(()=>setMsg(''),1800); };
  const node = html`<${AnimatePresence}>${msg && html`<${M.div} className="toast" role="status" aria-live="polite" key="t" initial=${{opacity:0,y:16}} animate=${{opacity:1,y:0}} exit=${{opacity:0,y:16}} transition=${{duration:.25}}>${msg}<//>`}<//>`;
  return [show,node];
}
function Bg({i}){ return html`<div className="bg"><img src=${IMG.bg[i%IMG.bg.length]} alt="" loading="lazy"/><div className="shade"></div><div className="tint"></div></div>`; }
function Tilt({children, className, max=10}){
  const reduce=useReducedMotion(); const rx=Motion.useMotionValue(0), ry=Motion.useMotionValue(0);
  const srx=useSpring(rx,{stiffness:120,damping:16}), sry=useSpring(ry,{stiffness:120,damping:16});
  const onMove=(e)=>{ if(reduce) return; const r=e.currentTarget.getBoundingClientRect(); const px=(e.clientX-r.left)/r.width-.5, py=(e.clientY-r.top)/r.height-.5; ry.set(px*max*2); rx.set(-py*max*2); };
  const onLeave=()=>{ rx.set(0); ry.set(0); };
  return html`<${M.div} className=${className} style=${{rotateX:srx,rotateY:sry,transformStyle:'preserve-3d'}} onMouseMove=${onMove} onMouseLeave=${onLeave}>${children}<//>`;
}

/* ===================== embers (cover) ===================== */
function Embers(){
  const ref=useRef(null); const reduce=useReducedMotion();
  useEffect(()=>{ if(reduce) return; const c=ref.current, ctx=c.getContext('2d'); let w,h,raf,parts=[]; const dpr=Math.min(2,window.devicePixelRatio||1);
    const size=()=>{ const r=c.getBoundingClientRect(); w=r.width; h=r.height; c.width=w*dpr; c.height=h*dpr; ctx.setTransform(dpr,0,0,dpr,0,0); };
    const spawn=()=>({x:Math.random()*w,y:h+10,r:.8+Math.random()*2.2,vy:.25+Math.random()*.7,vx:(Math.random()-.5)*.35,life:0,max:380+Math.random()*420,hue:22+Math.random()*20});
    size(); for(let i=0;i<Math.floor(w*h/9000);i++){const p=spawn();p.y=Math.random()*h;p.life=Math.random()*p.max;parts.push(p);}
    const tick=()=>{ ctx.clearRect(0,0,w,h); for(const p of parts){ p.life++; p.x+=p.vx+Math.sin((p.life+p.y)*.015)*.25; p.y-=p.vy; const a=Math.sin(Math.PI*Math.min(1,p.life/p.max)); if(p.life>p.max||p.y<-10){Object.assign(p,spawn());continue;} ctx.beginPath(); ctx.fillStyle=`hsla(${p.hue},95%,60%,${a*.85})`; ctx.shadowColor=`hsla(${p.hue},100%,55%,${a})`; ctx.shadowBlur=8; ctx.arc(p.x,p.y,p.r,0,Math.PI*2); ctx.fill(); } raf=requestAnimationFrame(tick); };
    let on=false; const io=new IntersectionObserver(([en])=>{ if(en.isIntersecting&&!on){on=true; raf=requestAnimationFrame(tick);} else if(!en.isIntersecting&&on){on=false; cancelAnimationFrame(raf);} }); io.observe(c);
    const ro=new ResizeObserver(size); ro.observe(c); return ()=>{cancelAnimationFrame(raf); ro.disconnect(); io.disconnect();}; },[reduce]);
  return html`<canvas ref=${ref} aria-hidden="true"></canvas>`;
}
/* ===================== hash rain (telehash) ===================== */
function HashRain(){
  const ref=useRef(null); const reduce=useReducedMotion();
  useEffect(()=>{ if(reduce) return; const c=ref.current, ctx=c.getContext('2d'); let w,h,raf,cols=[]; const dpr=Math.min(2,window.devicePixelRatio||1); const HEX='0123456789abcdef'; const fs=14;
    const size=()=>{ const r=c.getBoundingClientRect(); w=r.width; h=r.height; c.width=w*dpr; c.height=h*dpr; ctx.setTransform(dpr,0,0,dpr,0,0); cols=Array.from({length:Math.ceil(w/(fs*1.2))},(_,i)=>({x:i*fs*1.2,y:Math.random()*h,v:.6+Math.random()*1.6})); };
    size(); ctx.font=`${fs}px Menlo, monospace`;
    const tick=()=>{ ctx.fillStyle='rgba(12,11,9,.18)'; ctx.fillRect(0,0,w,h); for(const col of cols){ const ch=HEX[Math.floor(Math.random()*16)]; ctx.fillStyle=Math.random()<.08?'#f7931a':'rgba(247,147,26,.28)'; ctx.fillText(ch,col.x,col.y); col.y+=col.v*fs*.6; if(col.y>h+20){col.y=-20; col.v=.6+Math.random()*1.6;} } raf=requestAnimationFrame(tick); };
    let on=false; const io=new IntersectionObserver(([en])=>{ if(en.isIntersecting&&!on){on=true; raf=requestAnimationFrame(tick);} else if(!en.isIntersecting&&on){on=false; cancelAnimationFrame(raf);} }); io.observe(c);
    const ro=new ResizeObserver(size); ro.observe(c); return ()=>{cancelAnimationFrame(raf); ro.disconnect(); io.disconnect();}; },[reduce]);
  return html`<canvas ref=${ref} aria-hidden="true" style=${{opacity:.55}}></canvas>`;
}

function HashTicker(){
  const reduce=useReducedMotion(); const HEX='0123456789abcdef';
  const rnd=(n)=>Array.from({length:n},()=>HEX[Math.floor(Math.random()*16)]).join('');
  const [h,setH]=useState(()=>rnd(44));
  useEffect(()=>{ if(reduce) return; const id=setInterval(()=>setH(rnd(44)),90); return ()=>clearInterval(id); },[reduce]);
  return html`<${React.Fragment}><div className="hashbox" aria-hidden="true"><span style=${{color:'var(--bone)'}}>Searching · </span><b>00000000000000000000</b>${h}<span style=${{color:'var(--bone)'}}> · example only · live hashrate on stage at the Summit</span></div><span className="screen-reader-text">Decorative hashing example; live hashrate is shown on stage at the Summit.</span><//>`;
}

/* ===================== slides ===================== */
function SplitTitle({words}){
  const reduce=useReducedMotion(); let i=0;
  return html`<h1 className="h-title" aria-label=${words.map(w=>w[0]).join(' ')}>
    ${words.map(([w,cls],wi)=>html`<span className=${'w '+(cls||'')} key=${wi} aria-hidden="true">
      ${w.split('').map(ch=>{ const idx=i++; return html`<${M.span} className="c" key=${idx} initial=${reduce?false:{y:'60%',opacity:0}} animate=${{y:'0%',opacity:1}} transition=${{duration:.7,ease,delay:.15+idx*.03}}>${ch}<//>`; })}
    </span>`)}
  </h1>`;
}
function Cover(){
  const reduce=useReducedMotion();
  return html`<section className="slide" id="s0">
    <${Bg} i=${0}/><${Embers}/>
    <div className="slide-in cover">
      <div>
        <${M.div} className="eyebrow" initial=${reduce?false:{opacity:0,x:-12}} animate=${{opacity:1,x:0}} transition=${{duration:.6}}>Bitcoin Veterans · Summit 2026<//>
        <${SplitTitle} words=${[['Sponsor'],['Deck','o']]}/>
        <${M.p} className="lede" initial=${reduce?false:{opacity:0,y:10}} animate=${{opacity:1,y:0}} transition=${{duration:.7,delay:.8}}>${rich(D.cover.lede)}<//>
        <${M.div} className="meta" initial=${reduce?false:{opacity:0}} animate=${{opacity:1}} transition=${{duration:.7,delay:1}}>
          <span>Dates <b>${D.event.dates}</b></span><span>Venue <b>${D.event.venue}</b></span><span>City <b>${D.event.city}</b></span>
        <//>
        <${M.div} className="cta" initial=${reduce?false:{opacity:0,y:14}} animate=${{opacity:1,y:0}} transition=${{duration:.7,delay:1.15}}>
          <a className="btn solid" href="#s2">See the tiers</a>
          <a className="btn" href=${FORM} target="_blank" rel="noopener">Sponsor form</a>
        <//>
      </div>
      <${M.div} initial=${reduce?false:{opacity:0,scale:.9,rotateY:-14}} animate=${{opacity:1,scale:1,rotateY:0}} transition=${{duration:1.1,delay:.4,ease}}>
        <${Tilt} className="banner" max=${8}><div className="glow"></div><img src=${IMG.banner} alt="Bitcoin Veterans Summit 2026 banner: Nashville, TN, Bitcoin Park, November 9–10, 2026"/><//>
      <//>
    </div>
  </section>`;
}
function Mission(){
  const ref=useRef(null); const inView=useInView(ref,{once:true,amount:.4});
  const S=D.mission.stats; const s0=useCountUp(S[0]?S[0].value:0,inView), s1=useCountUp(S[1]?S[1].value:0,inView), s2=useCountUp(S[2]?S[2].value:0,inView), s3=useCountUp(S[3]?S[3].value:0,inView,1200); const sv=[s0,s1,s2,s3];
  return html`<section className="slide" id="s1" ref=${ref}>
    <${Bg} i=${3}/>
    <div className="slide-in two">
      <${Reveal}>
        <div className="eyebrow">Mission brief</div>
        <h2>Why sponsor <span className="o">this</span> summit</h2>
        ${D.mission.paragraphs.map((p,k)=>html`<p className="lede" key=${k}>${rich(p)}</p>`)}
      <//>
      <${Reveal} delay=${.12}>
        <div className="stats">
          ${S.map((s,k)=>html`<div className="stat" key=${k}><div className="v">${sv[k]}${s.suffix||''}</div><div className="l">${s.label}</div></div>`)}
        </div>
        <div className="eyebrow" style=${{marginTop:24}}>Who is in the room</div>
        <ul className="who">
          ${D.mission.audience.map((a,k)=>html`<li key=${k}>${a}</li>`)}
        </ul>
      <//>
    </div>
  </section>`;
}
function Overview({go}){
  const [mode,setMode]=useState('cards'); const reduce=useReducedMotion();
  return html`<section className="slide" id="s2">
    <${Bg} i=${5}/>
    <div className="slide-in">
      <${Reveal}>
        <div className="eyebrow">The tiers</div>
        <h2>${numberWord(TIERS.length)} ways to <span className="o">back the mission</span></h2>
        <div className="seg" role="group" aria-label="View">
          ${[['cards','Tiers'],['matrix','Compare benefits']].map(([k,l])=>html`<button key=${k} type="button" aria-pressed=${mode===k} className=${mode===k?'on':''} onClick=${()=>setMode(k)}>
            ${mode===k && html`<${M.span} className="pill" layoutId="segpill" transition=${{type:'spring',stiffness:400,damping:32}}/>`}<span>${l}</span></button>`)}
        </div>
      <//>
      <${AnimatePresence} mode="wait">
        ${mode==='cards' ? html`<${M.div} key="cards" className="grid" initial=${reduce?false:{opacity:0}} animate=${{opacity:1}} exit=${{opacity:0}} transition=${{duration:.25}}>
          ${TIERS.map((t,i)=>{ const [cls,lab]=status(t); return html`<${M.button} type="button" key=${t.id} className=${'card'+(t.status==='sold'?' sold':'')} onClick=${()=>go(3+i)}
              initial=${reduce?false:{opacity:.3,y:16}} whileInView=${{opacity:1,y:0}} viewport=${{once:true}} transition=${{duration:.5,delay:i*.05,ease}}
              whileHover=${reduce?undefined:{y:-4,rotateX:3,rotateY:-3}} whileTap=${reduce?undefined:{scale:.98}}>
            <img className="badge" src=${t.badge==='ob'?IMG.ob:IMG.logo} alt=""/>
            <div className="name">${t.name}</div>
            <div className="price">${fmt(t.price)}${t.priceMax?html`<span style=${{fontSize:16,color:'var(--khaki)'}}> – ${fmt(t.priceMax)}</span>`:''}</div>
            <div className="sub">${t.tag}</div>
            <div><span className=${'tag '+cls}><i></i>${lab}</span></div>
            ${t.status==='sold' && html`<div className="stamp">Sold</div>`}
          <//>`; })}
        <//>` : html`<${M.div} key="matrix" className="matrix-wrap" initial=${reduce?false:{opacity:0,y:8}} animate=${{opacity:1,y:0}} exit=${{opacity:0}} transition=${{duration:.25}}>
          <table className="matrix"><thead><tr><th>Tier</th><th>Price</th>${COLS.map(([k,l])=>html`<th key=${k}>${l}</th>`)}</tr></thead>
          <tbody>${TIERS.map(t=>html`<tr key=${t.id} className=${t.status==='sold'?'sold':''}><td>${t.name}</td><td><span className="t">${fmt(t.price)}${t.priceMax?'+':''}</span></td>
            ${COLS.map(([k])=>html`<td key=${k}>${k==='tix' ? (t.m.tix?html`<span className="t">${t.m.tix}</span>`:'') : (t.m[k]?html`<span className="y"></span>`:'')}</td>`)}</tr>`)}</tbody></table>
        <//>`}
      <//>
      <div className="note">Tap a tier to jump to its slide · all levels via the sponsor form</div>
    </div>
  </section>`;
}
function Medal({name,logo,alt}){
  const reduce=useReducedMotion();
  return html`<${Tilt} className="medal" max=${12}>
    <div className="disc"></div>
    ${!reduce && html`<${M.div} className="ring" animate=${{rotate:360}} transition=${{repeat:Infinity,duration:40,ease:'linear'}}/>`}
    <div className="ring2"></div>
    <${M.div} className="core" initial=${reduce?false:{scale:.6,opacity:0}} whileInView=${{scale:1,opacity:1}} viewport=${{once:true}} transition=${{type:'spring',stiffness:200,damping:18}}><img src=${logo||IMG.logo} alt=${alt||'Bitcoin Veterans'}/><//>
    <div className="lbl">${name}</div>
  <//>`;
}
function Price({t}){
  const ref=useRef(null); const inView=useInView(ref,{once:true,amount:.5}); const v=useCountUp(t.price,inView,1000);
  return html`<div className="price-big" ref=${ref}>${fmt(v)}${t.priceMax?html`<small>to ${fmt(t.priceMax)} · 1 to 200 veterans</small>`:''}<//>`;
}
function VeteranSlider(){
  const [n,setN]=useState(10);
  return html`<div className="slider">
    <label htmlFor="vets">How many veterans will you cover?</label>
    <input id="vets" type="range" min="1" max="200" value=${n} onInput=${e=>setN(+e.target.value)} aria-valuetext=${n+' veterans, '+fmt(n*200)}/>
    <div className="row"><div className="big">${n}<small>veteran${n>1?'s':''}</small></div><div className="big">${fmt(n*200)}<small>covers their tickets</small></div></div>
    <div className="seats" aria-hidden="true">${Array.from({length:200},(_,i)=>html`<i key=${i} className=${i<n?'on':''}></i>`)}</div>
  </div>`;
}
function Tier({t,i,bgi}){
  const reduce=useReducedMotion(); const [cls,lab]=status(t); const sold=t.status==='sold';
  return html`<section className="slide" id=${'s'+(3+i)}>
    <${Bg} i=${bgi}/>${t.feature==='telehash' && html`<${HashRain}/>`}
    <div className="slide-in tier">
      <${Reveal} y=${0}><${Medal} name=${t.name} logo=${t.badge==='ob'?IMG.ob:IMG.logo} alt=${t.badge==='ob'?'Operation Bitcoin':'Bitcoin Veterans'}/><//>
      <div>
        <${Reveal}>
          <div className="eyebrow">Tier ${i+1} of ${TIERS.length} · ${t.name}${t.feature==='veteran'?'':' sponsorship'}</div>
          <h2>${t.name}${t.feature==='veteran'?'':' '}<span className="o">${t.feature==='telehash'?'· live hashrate':t.feature==='veteran'?'':'sponsorship'}</span></h2>
          <${Price} t=${t}/>
          <div style=${{marginTop:12}}><span className=${'tag '+cls}><i></i>${lab}</span>${(t.remain && t.slotsTotal) ? html` <span className="tag">${t.remain} of ${t.slotsTotal} left</span>` : null}</div>
          ${t.intro && html`<p className="lede">${t.intro}</p>`}
        <//>
        <ul className="bens">
          ${t.bens.map((b,k)=>html`<${M.li} key=${k} initial=${reduce?false:{opacity:.3,x:-10}} whileInView=${{opacity:1,x:0}} viewport=${{once:true}} transition=${{duration:.45,delay:.15+k*.06,ease}}>${b}<//>`)}
        </ul>
        ${t.feature==='telehash' && html`<${HashTicker}/>`}
        ${t.feature==='veteran' && html`<${VeteranSlider}/>`}
        <div className="cta">
          ${sold ? html`<span className="btn ghost" aria-disabled="true">This level is purchased</span><a className="btn" href="#s2">See open tiers</a>`
                 : html`<a className="btn solid" href=${FORM} target="_blank" rel="noopener">Sponsor at this level</a><a className="btn ghost" href="#s2">All tiers</a>`}
        </div>
      </div>
    </div>
  </section>`;
}
function Tickets(){
  const reduce=useReducedMotion();
  const tk=D.tickets;
  return html`<section className="slide" id=${'s'+(3+TIERS.length)}>
    <${Bg} i=${12}/>
    <div className="slide-in">
      <${Reveal}><div className="eyebrow">Tickets · get them now</div><h2>Not sponsoring? <span className="o">Come anyway.</span></h2><//>
      <div className="tix3">${tk.map((k,i)=>html`<${M.a} key=${i} className=${'tk'+(k.best?' best':'')} href=${k.url} target="_blank" rel="noopener" style=${{textDecoration:'none',color:'inherit'}}
          initial=${reduce?false:{opacity:.3,y:16}} whileInView=${{opacity:1,y:0}} viewport=${{once:true}} transition=${{duration:.5,delay:i*.08,ease}} whileHover=${reduce?undefined:{y:-4}}>
        ${k.best && html`<span className="best-tag">Best deal</span>`}
        <div className="d">${k.day}</div><h3>${k.title}</h3><div className="p">${k.price}</div><p>${k.text}</p><span className="btn" style=${{justifySelf:'start'}}>Buy ticket</span>
      <//>`)}</div>
      <div className="note">${D.ticketsNote}</div>
    </div>
  </section>`;
}
function Contact({copy}){
  const C=D.contact;
  return html`<section className="slide" id=${'s'+(4+TIERS.length)}>
    <${Bg} i=${11}/>
    <div className="slide-in">
      <${Reveal}><div className="eyebrow">Get on the mission</div><h2>Ready to <span className="o">sponsor?</span></h2>
        <p className="lede">Every level is claimed through one form. Pick a tier, tell us who you are, and we will follow up with the details and the invoice.</p>
        <div className="cta"><a className="btn solid" href=${FORM} target="_blank" rel="noopener">Open the sponsor form</a><a className="btn ghost" href=${TICKETS}>Get tickets</a></div>
      <//>
      <div className="contact">
        <${Reveal} delay=${.1}><div className="cc">
          <h3>${C.heading}</h3>
          <div style=${{display:'flex',gap:14,alignItems:'center'}}>${C.photo && html`<img className="photo" src=${C.photo} alt=${C.name}/>`}<div><div className="big">${C.name}</div><div style=${{color:'var(--khaki)',fontSize:14}}>${C.org}</div></div></div>
          <div className="cta" style=${{marginTop:6}}><a className="btn" href=${'mailto:'+C.email+(C.emailSubject?'?subject='+encodeURIComponent(C.emailSubject):'')}>${C.email}</a>${C.signal && html`<button type="button" className="btn ghost" onClick=${()=>copy(C.signal,'Signal handle')}>Signal · ${C.signal}</button>`}</div>
        </div><//>
        <${Reveal} delay=${.2}><div className="cc">
          <h3>${C.teamHeading}</h3>
          <div className="big">${C.teamEmail}</div>
          <p style=${{margin:0,color:'var(--bone-2)'}}>${C.teamText}</p>
          <div className="cta" style=${{marginTop:6}}><a className="btn" href=${'mailto:'+C.teamEmail}>Email the team</a><button type="button" className="btn ghost" onClick=${()=>copy(FORM,'Form link')}>Copy form link</button></div>
        </div><//>
      </div>
      <div className="foot">${D.footer}</div>
    </div>
  </section>`;
}

/* ===================== app / chrome ===================== */
const LABELS = ['Cover','Mission','Tiers',...TIERS.map(t=>t.name),'Tickets','Contact'];
function App(){
  const deckRef=useRef(null);
  const { scrollYProgress } = useScroll({ container: deckRef });
  const sx=useSpring(scrollYProgress,{stiffness:120,damping:24,mass:.3});
  const [cur,setCur]=useState(0);
  const [toast,toastNode]=useToast();
  const copy=async(txt,label)=>{ try{ await navigator.clipboard.writeText(txt); toast(label+' copied'); }catch(e){ toast('Copy blocked — select the text instead'); } };
  const N=LABELS.length; const target=useRef(0); const lock=useRef(0);
  const go=useCallback((i,instant)=>{ const k=Math.max(0,Math.min(N-1,i)); target.current=k; lock.current=Date.now()+900; const el=document.getElementById('s'+k); const root=deckRef.current; if(el&&root) root.scrollTo({top:el.offsetTop,behavior:instant?'auto':'smooth'}); },[N]);
  useEffect(()=>{
    const root=deckRef.current; const els=LABELS.map((_,i)=>document.getElementById('s'+i)).filter(Boolean);
    const io=new IntersectionObserver((ents)=>{ ents.forEach(en=>{ if(en.isIntersecting){ const k=+en.target.id.slice(1); setCur(k); if(Date.now()>lock.current) target.current=k; } }); },{root,rootMargin:'-45% 0px -45% 0px',threshold:0});
    els.forEach(el=>io.observe(el));
    const click=(e)=>{ const a=e.target.closest('a[href^="#s"]'); if(!a) return; const k=+a.getAttribute('href').slice(2); if(Number.isFinite(k)){ e.preventDefault(); go(k); } };
    root.addEventListener('click',click);
    if(location.hash&&/^#s\d+$/.test(location.hash)) go(+location.hash.slice(2),true);
    const key=(e)=>{ if(e.defaultPrevented||e.altKey||e.ctrlKey||e.metaKey) return; const tg=e.target; if(tg&&tg.tagName&&['INPUT','SELECT','TEXTAREA'].includes(tg.tagName)) return; if((e.key===' '||e.key==='Enter')&&tg&&tg.closest&&tg.closest('a,button,input,select,textarea,summary,[contenteditable],[role="slider"]')) return; if(['ArrowDown','PageDown',' ','ArrowRight'].includes(e.key)){ e.preventDefault(); go(target.current+1);} if(['ArrowUp','PageUp','ArrowLeft'].includes(e.key)){ e.preventDefault(); go(target.current-1);} if(e.key==='Home'){go(0);} if(e.key==='End'){go(N-1);} };
    window.addEventListener('keydown',key); return ()=>{ io.disconnect(); root.removeEventListener('click',click); window.removeEventListener('keydown',key); };
  },[cur,go,N]);
  return html`<div className="deck" ref=${deckRef}>
    <${M.div} className="progress" style=${{scaleX:sx}}/>
    <div className="topbar">
      <a className="brand" href="#s0">BV <b>SUMMIT 2026</b><span className="sfx"> · Sponsor deck</span></a>
      <a className="back" href=${TICKETS}>← Summit 2026</a>
      <span className="n">${String(cur+1).padStart(2,'0')} / ${String(N).padStart(2,'0')} · ${LABELS[cur]}</span>
      <a className="tix" href=${FORM} target="_blank" rel="noopener">Sponsor form</a>
    </div>
    <div className="dots" role="navigation" aria-label="Slides">${LABELS.map((l,i)=>html`<button key=${i} type="button" className=${cur===i?'on':''} onClick=${()=>go(i)} aria-label=${l} aria-current=${cur===i}><i></i><span>${l}</span></button>`)}</div>
    <div className="navbtns"><button type="button" onClick=${()=>go(target.current-1)} aria-label="Previous slide">↑</button><button type="button" onClick=${()=>go(target.current+1)} aria-label="Next slide">↓</button></div>
    <div className="hint"><b>↑ ↓</b> or space to move · <b>Home</b> / <b>End</b></div>

    <${Cover}/>
    <${Mission}/>
    <${Overview} go=${go}/>
    ${TIERS.map((t,i)=>html`<${Tier} key=${t.id} t=${t} i=${i} bgi=${1+i}/>`)}
    <${Tickets}/>
    <${Contact} copy=${copy}/>
    ${toastNode}
  </div>`;
}
ReactDOM.createRoot(document.getElementById('root')).render(html`<${App}/>`);
})();
