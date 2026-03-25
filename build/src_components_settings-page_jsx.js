"use strict";
(globalThis["webpackChunkwp_ai_sdk_demo"] = globalThis["webpackChunkwp_ai_sdk_demo"] || []).push([["src_components_settings-page_jsx"],{

/***/ "./src/components/settings-page.jsx"
/*!******************************************!*\
  !*** ./src/components/settings-page.jsx ***!
  \******************************************/
(module, __webpack_exports__, __webpack_require__) {

__webpack_require__.a(module, async (__webpack_handle_async_dependencies__, __webpack_async_result__) => { try {
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   SettingsPage: () => (/* binding */ SettingsPage)
/* harmony export */ });
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _wordpress_core_data__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/core-data */ "@wordpress/core-data");
/* harmony import */ var _wordpress_core_data__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_core_data__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _wordpress_dataviews_wp__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @wordpress/dataviews/wp */ "./node_modules/@wordpress/dataviews/build-wp/index.js");
/* harmony import */ var _wordpress_html_entities__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! @wordpress/html-entities */ "@wordpress/html-entities");
/* harmony import */ var _wordpress_html_entities__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(_wordpress_html_entities__WEBPACK_IMPORTED_MODULE_6__);
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__);








// Uses /* webpackIgnore: true */ to tell webpack to skip bundling this import and leave it as a runtime ES module import.
// Needed until https://github.com/WordPress/gutenberg/issues/75196 is fixed

const {
  getAbility,
  executeAbility
} = await import(/* webpackIgnore: true */'@wordpress/abilities');
const SettingsTitle = () => {
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.__experimentalHeading, {
    level: 1,
    children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('WP AI SDK Demo', 'wp-ai-client-demo')
  });
};
const GenerateButton = ({
  onClick
}) => {
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("div", {
    children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
      variant: "primary",
      onClick: onClick,
      __next40pxDefaultSize: true,
      children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Generate', 'wp-ai-client-demo')
    })
  });
};
const GenerateWritingStyleButton = ({
  onClick
}) => {
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("div", {
    children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
      variant: "primary",
      onClick: onClick,
      __next40pxDefaultSize: true,
      children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Generate Writing Style', 'wp-ai-client-demo')
    })
  });
};
const SettingsPage = () => {
  const [noticeStatus, setNoticeStatus] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useState)('info');
  const [noticeMessage, setNoticeMessage] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useState)('Ready...');
  const [input, setInput] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useState)({
    title: "",
    prompt: "",
    writingStyle: "",
    context: []
  });
  const posts = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_3__.useSelect)(select => {
    return select(_wordpress_core_data__WEBPACK_IMPORTED_MODULE_4__.store).getEntityRecords('postType', 'post', {
      per_page: 25,
      orderby: 'date',
      order: 'desc',
      status: 'publish'
    });
  }, []);
  const postElements = (posts || []).map(post => ({
    value: post.id,
    label: (0,_wordpress_html_entities__WEBPACK_IMPORTED_MODULE_6__.decodeEntities)(post.title.rendered)
  }));
  const fields = [{
    id: 'title',
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Title', 'wp-ai-client-demo'),
    type: 'text'
  }, {
    id: 'prompt',
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Prompt', 'wp-ai-client-demo'),
    type: 'text',
    Edit: 'textarea'
  }, {
    id: 'writingStyle',
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Writing Style', 'wp-ai-client-demo'),
    type: 'text',
    Edit: 'textarea'
  }, {
    id: "context",
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Context (Optional), Select Posts to Include', 'wp-ai-client-demo'),
    Edit: ({
      data,
      field,
      onChange
    }) => {
      const selected = data.context || [];
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsxs)("fieldset", {
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("legend", {
          children: field.label
        }), postElements.map(element => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.CheckboxControl, {
          label: element.label,
          checked: selected.includes(element.value),
          onChange: isChecked => {
            const updated = isChecked ? [...selected, element.value] : selected.filter(v => v !== element.value);
            onChange({
              context: updated
            });
          }
        }, element.value))]
      });
    }
  }];
  const generateForm = {
    fields: ['title', 'prompt']
  };
  const writingStyleForm = {
    fields: ['writingStyle', 'context']
  };
  const tabs = [{
    name: 'generate',
    title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Generate Post', 'wp-ai-client-demo')
  }, {
    name: 'writing-style',
    title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Writing Style', 'wp-ai-client-demo')
  }];
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useEffect)(() => {
    async function loadInstructionsMessage() {
      let prompt = '';
      prompt += 'A simple sentence encouraging the user to create a WordPress Post using AI. ';
      prompt += 'Only return the actual sentence. Do not include any additional text or formatting.';
      const text = await wp.aiClient.prompt(prompt).generateText();
      setNoticeMessage(text);
    }
    loadInstructionsMessage();
    async function loadWritingStyle() {
      const ability = getAbility('wp-ai-client-demo/get-writing-style');
      if (!ability) {
        console.error('get-writing-style ability not available.');
        return;
      }
      try {
        const result = await executeAbility('wp-ai-client-demo/get-writing-style', {});
        if (result?.instructions) {
          setInput(current => ({
            ...current,
            writingStyle: result.instructions
          }));
        }
      } catch (err) {
        console.error('Failed to load writing style:', err);
      }
    }
    loadWritingStyle();
  }, []);
  const updateNotice = (message, status = 'info') => {
    setNoticeMessage(message);
    setNoticeStatus(status);
  };
  const onChange = edits => {
    setInput(current => ({
      ...current,
      ...edits
    }));
  };
  const generateFromInput = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useCallback)(async () => {
    const generatePostAbility = getAbility('wp-ai-client-demo/generate-post');
    if (!generatePostAbility) {
      updateNotice('Whoops, post generation Ability not found.', 'error');
      return;
    }
    try {
      updateNotice('Attempting to execute post generation Ability, please hold for updates...', 'info');
      const result = await executeAbility('wp-ai-client-demo/generate-post', {
        title: input.title,
        prompt: input.prompt
      });
      console.log(result);
    } catch (err) {
      updateNotice('Error during post generation. Check console for details.', 'error');
      console.error(err);
    } finally {
      updateNotice('Post generation completed!.', 'success');
    }
  }, [input]);
  const generateWritingStyle = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useCallback)(async () => {
    const writingStyleAbility = getAbility('wp-ai-client-demo/generate-writing-style');
    if (!writingStyleAbility) {
      updateNotice('Whoops, writing style generation Ability not found.', 'error');
      return;
    }
    if (!input.context || input.context.length === 0) {
      updateNotice('Please select at least one post to analyze.', 'error');
      return;
    }
    try {
      updateNotice('Generating writing style from selected posts, please hold for updates...', 'info');
      const result = await executeAbility('wp-ai-client-demo/generate-writing-style', {
        post_ids: input.context
      });
      console.log(result);
      if (result?.instructions) {
        onChange({
          writingStyle: result.instructions
        });
      }
    } catch (err) {
      updateNotice('Error during writing style generation. Check console for details.', 'error');
      console.error(err);
    } finally {
      updateNotice('Writing style generated and saved!', 'success');
    }
  }, [input]);
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsxs)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.__experimentalVStack, {
    spacing: 4,
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(SettingsTitle, {}), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Notice, {
      status: noticeStatus,
      children: noticeMessage
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.TabPanel, {
      tabs: tabs,
      children: tab => {
        if (tab.name === 'generate') {
          return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsxs)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.__experimentalVStack, {
            spacing: 4,
            children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_dataviews_wp__WEBPACK_IMPORTED_MODULE_5__.DataForm, {
              data: input,
              fields: fields,
              form: generateForm,
              onChange: onChange
            }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(GenerateButton, {
              onClick: generateFromInput
            })]
          });
        }
        if (tab.name === 'writing-style') {
          return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsxs)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.__experimentalVStack, {
            spacing: 4,
            children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_dataviews_wp__WEBPACK_IMPORTED_MODULE_5__.DataForm, {
              data: input,
              fields: fields,
              form: writingStyleForm,
              onChange: onChange
            }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(GenerateWritingStyleButton, {
              onClick: generateWritingStyle
            })]
          });
        }
      }
    })]
  });
};

__webpack_async_result__();
} catch(e) { __webpack_async_result__(e); } }, 1);

/***/ }

}]);
//# sourceMappingURL=src_components_settings-page_jsx.js.map?ver=4e32966bce66632bf279