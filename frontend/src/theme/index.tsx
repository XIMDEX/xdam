import { createMuiTheme } from '@material-ui/core/styles';

const theme = createMuiTheme({
  breakpoints: {
    values: {
      xs: 0,
      sm: 600,
      md: 960,
      lg: 1280,
      xl: 1700,
    },
  },
  palette: {
    primary: {
      main: '#43a1a2',
    },
    secondary: {
      main: '#068873',
    },
    info: {
      main: '#566BC8',
    },
    success: {
      main: '#05C7AA',
    },
    error: {
      main: '#FF3333'
    },
    warning: {
      main: '#FEF972'
    }
  },
  typography: {
    fontFamily: 'Roboto, Raleway, Arial',
  },
  overrides: {
    // Style sheet name ⚛️
    MuiButton: {
      // Name of the rule
      label: {
        // Some CSS
        //color: 'yellow',
      },
      text: {
        color: 'red',
      },
      textPrimary: {
        color: '#2196f3',
      },
      textSecondary: {

      },
      outlined: {
        // color: 'violet',
      },
      outlinedPrimary: {
      },
      outlinedSecondary: {

      },
      contained: {

      },
      containedPrimary: {

      },
      containedSecondary: {

      },
    }
  },
});

export default theme;